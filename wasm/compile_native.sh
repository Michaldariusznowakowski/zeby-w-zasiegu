#!/bin/bash
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'
if [[ $* == *--clean* ]]; then
  echo -e "${YELLOW}Removing the build directory${NC}"
  rm -rf build_native
fi
if [[ $* == "" ]]; then
  echo -e "${RED}Please provide the arguments${NC}"
  echo -e "${YELLOW}Use --help to see the available options${NC}"
  exit 0
fi
if [[ $* == *--help* ]]; then
  echo -e "${YELLOW} --clean to remove the build directory and start from scratch ${NC}"
  echo -e "${YELLOW} --build to build the project ${NC}"
  echo -e "${YELLOW} --run to start build project ${NC}"
  exit 0
fi
if [[ $* == *--build* ]]; then
  echo -e "${YELLOW}Building the project ${NC}"
  ROOT_DIR=$(pwd)
  if [ ! -d "build_native" ]; then
    mkdir build_native
    mkdir build_native/output
  fi
  cd build_native
  if [ ! -d "openssl" ]; then
    git clone https://github.com/openssl/openssl.git -b openssl-3.2.1
    if [ ! -d "openssl" ]; then
      echo -e "${RED}Failed to clone the openssl repository${NC}"
      cd ..
      rm -rf build
      exit 1
    fi
    export CC=gcc
    export CXX=g++
    cd openssl
    # debug flags
    ./Configure -static no-hw no-shared no-asm no-threads no-ssl3 no-dtls no-engine no-dso -ggdb
    make -j -ggdb 16 build_generated libssl.a libcrypto.a apps/openssl
  fi
    g++ -o $ROOT_DIR/build_native/output/OpenSSLWrapper.o $ROOT_DIR/src/main.cpp -L$ROOT_DIR/build_native/openssl/apps -L$ROOT_DIR/build_native/openssl -lssl -lcrypto -I$ROOT_DIR/build_native/openssl/include -DDEBUG -DNATIVE -ggdb -fsanitize=address -v -Wl,--whole-archive -lpthread -Wl,--no-whole-archive
  if [ ! -f $ROOT_DIR/build_native/output/OpenSSLWrapper.o ]; then
    echo -e "${RED}Failed to build the project${NC}"
    exit 1
  fi  
echo -e "${GREEN} Build script complete ${NC}"
if [[ $* == *--run* ]]; then
  echo -e "${YELLOW}Running software ${NC}"
  chmod +x $ROOT_DIR/build_native/output/OpenSSLWrapper.o
  $ROOT_DIR/build_native/output/OpenSSLWrapper.o
fi
fi