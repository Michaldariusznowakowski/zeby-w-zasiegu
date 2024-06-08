#include <openssl/bio.h>
#include <openssl/conf.h>
#include <openssl/err.h>
#include <openssl/evp.h>
#include <openssl/pem.h>
#include <openssl/rand.h>
#include <openssl/rsa.h>
#include <openssl/ssl.h>
#ifdef NATIVE
#include <chrono>
#include <fstream>
#include <iostream>
#endif
#include <string>
#include <vector>
#ifdef __EMSCRIPTEN__
#include <emscripten/bind.h>
using namespace emscripten;
#else
#define EMSCRIPTEN_BINDINGS(...)
#endif
#ifdef DEBUG
#include <iostream>
#endif
void sendError(std::string message) {
#ifdef DEBUG
  std::cerr << message << std::endl;
#endif
}
const std::string FILENAME = "file.bin";
EVP_CIPHER *CIPHER = NULL;
int initAES256() {
  CIPHER = EVP_CIPHER_fetch(NULL, "aes-256-ctr", NULL);
  if (!CIPHER) {
    sendError("Error initializing AES256");
    return -1;
  }
  return 0;
}
int destroyAES256() {
  if (CIPHER) {
    EVP_CIPHER_free(CIPHER);
    CIPHER = NULL;
  }
  return 0;
}
int generateRSAKey(std::vector<uint8_t> &key) {
  int status = 0;
  BIO *bio = NULL;
  EVP_PKEY_CTX *ctx = NULL;
  char *pem_key = NULL;
  long keylen = 0;
  EVP_PKEY *pkey = NULL;
  ctx = EVP_PKEY_CTX_new_id(EVP_PKEY_RSA, NULL);
  if (EVP_PKEY_keygen_init(ctx) <= 0) {
    sendError("Error initializing RSA keygen");
    status = -1;
    goto err;
  }
  if (EVP_PKEY_CTX_set_rsa_keygen_bits(ctx, 4096) <= 0) {
    sendError("Error setting RSA keygen bits");
    status = -2;
    goto err;
  }
  if (EVP_PKEY_keygen(ctx, &pkey) <= 0) {
    sendError("Error generating RSA key");
    status = -3;
    goto err;
  }
  bio = BIO_new(BIO_s_mem());
  if (!PEM_write_bio_PrivateKey(bio, pkey, NULL, NULL, 0, NULL, NULL)) {
    sendError("Error writing private key");
    status = -4;
    goto err;
  }
  keylen = BIO_get_mem_data(bio, &pem_key);
  for (int i = 0; i < keylen; i++) {
    uint8_t cd = static_cast<uint8_t>(pem_key[i]);
    key.push_back(cd);
  }
err:
  if (bio != NULL) {
    BIO_free(bio);
    bio = NULL;
  }
  if (pkey != NULL) {
    EVP_PKEY_free(pkey);
    pkey = NULL;
  }
  if (ctx != NULL) {
    EVP_PKEY_CTX_free(ctx);
    ctx = NULL;
  }
  return status;
}
int generatePublicKey(std::vector<uint8_t> &privateKey,
                      std::vector<uint8_t> &publicKey) {
  int status = 0;
  BIO *bio = NULL;
  BIO *out = NULL;
  EVP_PKEY *pkey = NULL;
  char *pem_key;
  long key_len;
  bio = BIO_new(BIO_s_mem());
  BIO_write(bio, privateKey.data(), privateKey.size());
  pkey = PEM_read_bio_PrivateKey(bio, NULL, NULL, NULL);
  if (!pkey) {
    sendError("Error reading private key");
    status = -1;
    goto err;
  }
  out = BIO_new(BIO_s_mem());
  if (!PEM_write_bio_PUBKEY(out, pkey)) {
    sendError("Error writing public key");
    status = -2;
    goto err;
  }
  key_len = BIO_get_mem_data(out, &pem_key);
  for (int i = 0; i < key_len; i++) {
    uint8_t cd = static_cast<uint8_t>(pem_key[i]);
    publicKey.push_back(cd);
  }
err:
  if (bio != NULL) {
    BIO_free(bio);
    bio = NULL;
  }
  if (out != NULL) {
    BIO_free(out);
    out = NULL;
  }
  if (pkey != NULL) {
    EVP_PKEY_free(pkey);
    pkey = NULL;
  }
  return status;
}
int signMessage(const std::vector<uint8_t> &privateKey,
                const std::vector<uint8_t> &message,
                std::vector<uint8_t> &outSignature) {
  int status = 0;
  BIO *bio = NULL;
  EVP_PKEY *pkey = NULL;
  EVP_MD_CTX *ctx = NULL;
  bio = BIO_new(BIO_s_mem());
  BIO_write(bio, privateKey.data(), privateKey.size());
  pkey = PEM_read_bio_PrivateKey(bio, NULL, NULL, NULL);
  if (!pkey) {
    sendError("Error reading private key");
    status = -1;
    goto err;
  }
  ctx = EVP_MD_CTX_new();
  if (EVP_DigestSignInit(ctx, NULL, EVP_sha256(), NULL, pkey) <= 0) {
    sendError("Error initializing signature");
    status = -2;
    goto err;
  }
  if (EVP_DigestSignUpdate(ctx, message.data(), message.size()) <= 0) {
    sendError("Error updating signature");
    status = -3;
    goto err;
  }
  size_t sig_len;
  if (EVP_DigestSignFinal(ctx, NULL, &sig_len) <= 0) {
    sendError("Error finalizing signature");
    status = -4;
    goto err;
  }
  outSignature.resize(sig_len);
  if (EVP_DigestSignFinal(ctx, outSignature.data(), &sig_len) <= 0) {
    sendError("Error finalizing signature");
    status = -5;
    goto err;
  }
err:
  if (ctx != NULL) {
    EVP_MD_CTX_free(ctx);
    ctx = NULL;
  }
  if (bio != NULL) {
    BIO_free(bio);
    bio = NULL;
  }
  if (pkey != NULL) {
    EVP_PKEY_free(pkey);
    pkey = NULL;
  }
  return status;
}
int verifySignature(const std::vector<uint8_t> &publicKey,
                    const std::vector<uint8_t> &message,
                    const std::vector<uint8_t> &signature) {
  int status = 0;
  BIO *bio = NULL;
  EVP_PKEY *pkey = NULL;
  EVP_MD_CTX *ctx = NULL;
  bio = BIO_new(BIO_s_mem());
  BIO_write(bio, publicKey.data(), publicKey.size());
  pkey = PEM_read_bio_PUBKEY(bio, NULL, NULL, NULL);
  if (!pkey) {
    sendError("Error reading public key");
    status = -1;
    goto err;
  }
  ctx = EVP_MD_CTX_new();
  if (EVP_DigestVerifyInit(ctx, NULL, EVP_sha256(), NULL, pkey) <= 0) {
    sendError("Error initializing verification");
    status = -2;
    goto err;
  }
  if (EVP_DigestVerifyUpdate(ctx, message.data(), message.size()) <= 0) {
    sendError("Error updating verification");
    status = -3;
    goto err;
  }
  if (EVP_DigestVerifyFinal(ctx, signature.data(), signature.size()) <= 0) {
    sendError("Error finalizing verification");
    status = -4;
    goto err;
  }
err:
  if (ctx != NULL) {
    EVP_MD_CTX_free(ctx);
    ctx = NULL;
  }
  if (bio != NULL) {
    BIO_free(bio);
    bio = NULL;
  }
  if (pkey != NULL) {
    EVP_PKEY_free(pkey);
    pkey = NULL;
  }
  return status;
}
int fileToVector(std::vector<uint8_t> &data) {
  FILE *file = fopen(FILENAME.c_str(), "rb");
  if (!file) {
    sendError("Error opening file");
    return -1;
  }
  fseek(file, 0, SEEK_END);
  long size = ftell(file);
  fseek(file, 0, SEEK_SET);
  data.resize(size);
  fread(data.data(), 1, size, file);
  fclose(file);
  return 0;
}
int vectorToFile(const std::vector<uint8_t> &data) {
  FILE *file = fopen(FILENAME.c_str(), "wb");
  if (!file) {
    sendError("Error opening file");
    return -1;
  }
  fwrite(data.data(), 1, data.size(), file);
  fclose(file);
  return 0;
}
int encryptRSA(const std::vector<uint8_t> &publicKey,
               const std::vector<uint8_t> &secPublicKey,
               const std::vector<uint8_t> &message,
               std::vector<uint8_t> &dataOut, std::vector<uint8_t> &ivOut,
               std::vector<uint8_t> &ekOut, std::vector<int> &eklOut) {
  if (CIPHER == NULL) {
    sendError("Error initializing AES256 someone forgot to call initAES256");
    return -1;
  }

  int status = 0;
  BIO *bio = NULL;
  BIO *bio2 = NULL;
  EVP_PKEY *keys[2] = {NULL, NULL};
  EVP_CIPHER_CTX *ctx = NULL;
  int *ekl = NULL;
  unsigned char **ek = NULL;
  int dataOutLen = 0;
  int dataOutLen2 = 0;
  int npubk = 0;
  unsigned char iv[EVP_CIPHER_iv_length(CIPHER)];

  bio = BIO_new(BIO_s_mem());
  BIO_write(bio, publicKey.data(), publicKey.size());
  keys[0] = PEM_read_bio_PUBKEY(bio, NULL, NULL, NULL);
  if (!keys[0]) {
    sendError("Error reading public key");
    status = -2;
    goto err;
  }
  npubk = 1;
  if (secPublicKey.size() > 0) {
    bio2 = BIO_new(BIO_s_mem());
    BIO_write(bio2, secPublicKey.data(), secPublicKey.size());
    keys[1] = PEM_read_bio_PUBKEY(bio2, NULL, NULL, NULL);
    if (!keys[1]) {
      sendError("Error reading public key 2");
      status = -3;
      goto err;
    }
    npubk = 2;
  }
  ek = new unsigned char *[npubk];
  for (int i = 0; i < npubk; i++) {
    ek[i] = new unsigned char[EVP_PKEY_size(keys[i])];
    memset(ek[i], 0, EVP_PKEY_size(keys[i]));
  }
  ekl = new int[npubk];
  memset(ekl, 0, npubk);

  ctx = EVP_CIPHER_CTX_new();

  if (EVP_SealInit(ctx, CIPHER, ek, ekl, iv, keys, npubk) <= 0) {
    sendError("Error initializing encryption");
    status = -6;
    goto err;
  }
  dataOut.resize(message.size() + EVP_CIPHER_block_size(CIPHER));
  if (EVP_SealUpdate(ctx, dataOut.data(), &dataOutLen, message.data(),
                     message.size()) <= 0) {
    sendError("Error updating encryption");
    status = -7;
    goto err;
  }
  if (EVP_SealFinal(ctx, dataOut.data() + dataOutLen, &dataOutLen2) <= 0) {
    sendError("Error finalizing encryption");
    status = -8;
    goto err;
  }
  dataOut.resize(dataOutLen + dataOutLen2);
  ekOut.resize(0);
  for (int i = 0; i < npubk; i++) {
    for (int j = 0; j < ekl[i]; j++) {
      ekOut.push_back(ek[i][j]);
    }
  }
  for (int i = 0; i < npubk; i++) {
    eklOut.push_back(ekl[i]);
  }
  ivOut.resize(EVP_CIPHER_iv_length(CIPHER));
  for (int i = 0; i < EVP_CIPHER_iv_length(CIPHER); i++) {
    ivOut[i] = iv[i];
  }
err:
  if (ctx != NULL) {
    EVP_CIPHER_CTX_free(ctx);
    ctx = NULL;
  }
  if (bio != NULL) {
    BIO_free(bio);
    bio = NULL;
  }
  if (bio2 != NULL) {
    BIO_free(bio2);
    bio2 = NULL;
  }
  for (int i = 0; i < npubk; i++) {
    if (keys[i] != NULL) {
      EVP_PKEY_free(keys[i]);
      keys[i] = NULL;
    }
    if (ek[i] != NULL) {
      delete[] ek[i];
      ek[i] = NULL;
    }
  }
  if (ekl != NULL) {
    delete[] ekl;
    ekl = NULL;
  }
  if (ek != NULL) {
    for (int i = 0; i < npubk; i++) {
      delete[] ek[i];
    }
    delete[] ek;
    ek = NULL;
  }
  return status;
}
int decryptRSA(const std::vector<uint8_t> &privateKeyVec,
               const std::vector<uint8_t> &dataVec,
               const std::vector<uint8_t> &ivVec,
               const std::vector<uint8_t> &ekVec, const std::vector<int> eklVec,
               std::vector<uint8_t> &dataOutVec) {
  if (CIPHER == NULL) {
    sendError("Error initializing AES256 someone forgot to call initAES256");
    return -1;
  }
  int status = 0;
  int errOpenInit = 0;
  BIO *bio = NULL;
  EVP_PKEY *pkey = NULL;
  EVP_CIPHER_CTX *ctx = NULL;
  unsigned char *ek1 = NULL;
  unsigned char *ek2 = NULL;
  int messageLen = 0;
  int messageLen2 = 0;
  unsigned char iv[EVP_CIPHER_iv_length(CIPHER)];
  for (int i = 0; i < EVP_CIPHER_iv_length(CIPHER); i++) {
    iv[i] = ivVec[i];
  }

  bio = BIO_new(BIO_s_mem());
  BIO_write(bio, privateKeyVec.data(), privateKeyVec.size());
  pkey = PEM_read_bio_PrivateKey(bio, NULL, NULL, NULL);
  if (!pkey) {
    sendError("Error reading private key");
    status = -2;
    goto err;
  }
  ctx = EVP_CIPHER_CTX_new();
  ek1 = new unsigned char[eklVec[0]];
  for (int i = 0; i < eklVec[0]; i++) {
    ek1[i] = ekVec[i];
  }
  dataOutVec.resize(dataVec.size() + EVP_CIPHER_block_size(CIPHER));
  errOpenInit = EVP_OpenInit(ctx, CIPHER, ek1, eklVec[0], iv, pkey);
  if (errOpenInit <= 0 && eklVec.size() == 2) {
    ek2 = new unsigned char[eklVec[1]];
    for (int i = 0; i < eklVec[1]; i++) {
      ek2[i] = ekVec[eklVec[0] + i];
    }
    errOpenInit = EVP_OpenInit(ctx, CIPHER, ek2, eklVec[1], iv, pkey);
  }
  if (errOpenInit <= 0) {
    sendError("Error initializing decryption");
    status = -3;
    goto err;
  }
  if (EVP_OpenUpdate(ctx, dataOutVec.data(), &messageLen, dataVec.data(),
                     dataVec.size()) <= 0) {
    sendError("Error updating decryption");
    status = -4;
    goto err;
  }
  if (EVP_OpenFinal(ctx, dataOutVec.data() + messageLen, &messageLen2) <= 0) {
    sendError("Error finalizing decryption");
    status = -5;
    goto err;
  }
  dataOutVec.resize(messageLen + messageLen2);
err:
  if (ctx != NULL) {
    EVP_CIPHER_CTX_free(ctx);
    ctx = NULL;
  }
  if (bio != NULL) {
    BIO_free(bio);
    bio = NULL;
  }
  if (pkey != NULL) {
    EVP_PKEY_free(pkey);
    pkey = NULL;
  }
  if (ek1 != NULL) {
    delete[] ek1;
    ek1 = NULL;
  }
  if (ek2 != NULL) {
    delete[] ek2;
    ek2 = NULL;
  }
  return status;
}
#ifdef __EMSCRIPTEN__
EMSCRIPTEN_BINDINGS(OpenSSLWrapper) {
  register_vector<uint8_t>("VectorUint8");
  register_vector<int>("VectorInt");
  function("generateRSAKey", &generateRSAKey);
  function("generatePublicKey", &generatePublicKey);
  function("signMessage", &signMessage);
  function("verifySignature", &verifySignature);
  function("encryptRSA", &encryptRSA);
  function("decryptRSA", &decryptRSA);
  function("initAES256", &initAES256);
  function("destroyAES256", &destroyAES256);
  function("fileToVector", &fileToVector);
  function("vectorToFile", &vectorToFile);
}
#endif

#ifdef NATIVE

std::chrono::time_point<std::chrono::system_clock> start;
void startTimer() { start = std::chrono::system_clock::now(); }
std::string getDuration() {
  // in microseconds
  auto end = std::chrono::system_clock::now();
  std::chrono::duration<double> elapsed_seconds = end - start;
  return std::to_string(elapsed_seconds.count());
}

std::vector<uint8_t> readFile(const std::string &filename) {
  std::ifstream file(filename, std::ios::binary);
  std::vector<uint8_t> data;
  if (file.is_open()) {
    file.seekg(0, std::ios::end);
    data.resize(file.tellg());
    file.seekg(0, std::ios::beg);
    file.read((char *)data.data(), data.size());
    file.close();
  }
  return data;
}

void saveFile(const std::string &filename, const std::vector<uint8_t> &data) {
  std::ofstream file(filename,
                     std::ios::out | std::ios::binary | std::ios::trunc);
  if (file.is_open()) {
    file.write((char *)data.data(), data.size());
    file.close();
  }
}

int main() {
  std::string strmessage =
      "Hello World! This is a test message. This message will be signed and "
      "then verified. If the signature is correct, then the message is "
      "authentic.";
  initAES256();
  std::cout << "Generating RSA key" << std::endl;
  std::vector<uint8_t> privateKey;
  startTimer();
  int status = generateRSAKey(privateKey);
  if (status < 0) {
    std::cerr << "Error generating RSA key" << std::endl;
    return status;
  }
  std::cout << "Time taken to generate RSA key: " << getDuration() << std::endl;
  std::cout << "Generating public key" << std::endl;
  startTimer();
  std::vector<uint8_t> publicKey;
  status = generatePublicKey(privateKey, publicKey);
  std::cout << "Time taken to generate public key: " << getDuration()
            << std::endl;
  if (status < 0) {
    std::cerr << "Error generating public key" << std::endl;
    return status;
  }
  std::cout << "Signing message" << std::endl;
  std::vector<uint8_t> message =
      std::vector<uint8_t>(strmessage.begin(), strmessage.end());
  std::vector<uint8_t> signature;

  startTimer();
  status = signMessage(privateKey, message, signature);
  std::cout << "Time taken to sign message: " << getDuration() << std::endl;
  if (status < 0) {
    std::cerr << "Error signing message" << std::endl;
    return status;
  }
  std::cout << "Verifying signature" << std::endl;
  startTimer();
  status = verifySignature(publicKey, message, signature);
  std::cout << "Time taken to verify signature: " << getDuration() << std::endl;
  if (status < 0) {
    std::cerr << "Error verifying signature" << std::endl;
    return status;
  }
  std::string files[] = {"file1mb.bin",  "file5mb.bin",   "file10mb.bin",
                         "file50mb.bin", "file100mb.bin", "file500mb.bin",
                         "file1gb.bin"};
  for (int i = 0; i < 7; i++) {
    std::cout << "Reading file " << files[i] << std::endl;
    std::vector<uint8_t> data = readFile(files[i]);
    std::vector<uint8_t> dataOut;
    std::vector<uint8_t> ivOut;
    std::vector<uint8_t> ekOut;
    std::vector<int> eklOut;
    std::cout << "Encrypting file" << std::endl;
    startTimer();
    status = encryptRSA(publicKey, {}, data, dataOut, ivOut, ekOut, eklOut);
    std::cout << "Time taken to encrypt file: " << getDuration() << std::endl;
    if (status < 0) {
      std::cerr << "Error encrypting file" << std::endl;
      return status;
    }
    std::cout << "Decrypting file" << std::endl;
    startTimer();
    std::vector<uint8_t> dataOut2;
    status = decryptRSA(privateKey, dataOut, ivOut, ekOut, eklOut, dataOut2);
    std::cout << "Time taken to decrypt file: " << getDuration() << std::endl;
    if (status < 0) {
      std::cerr << "Error decrypting file" << std::endl;
      return status;
    }
    if (dataOut2 != data) {
      std::cerr << "Decrypted file is not the same as the original file"
                << std::endl;
      return -1;
    }
  }
  std::cout << "Success" << std::endl;
  destroyAES256();
  return 0;
}
#endif