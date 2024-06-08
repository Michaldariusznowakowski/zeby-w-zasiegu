#!/bin/bash
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'
if [ -z "$EMSDK" ]; then
  echo -e "${RED}emsdk is not initialized${NC}"
  echo -e "${YELLOW}Please run the following command to initialize emsdk${NC}"
  echo -e "${YELLOW}source /path/to/emsdk/emsdk_env.sh${NC}"
  exit 1
fi
if [[ $* == *--clean* ]]; then
  echo -e "${YELLOW}Removing the build directory${NC}"
  rm -rf build
fi
if [[ $* == "" ]]; then
  echo -e "${RED}Please provide the arguments${NC}"
  echo -e "${YELLOW}Use --help to see the available options${NC}"
  exit 0
fi
if [[ $* == *--help* ]]; then
  echo -e "${YELLOW} --clean to remove the build directory and start from scratch ${NC}"
  echo -e "${YELLOW} --skip-move to skip moving the wasm file to the javascript directory ${NC}"
  echo -e "${YELLOW} --build to build the project ${NC}"
  echo -e "${YELLOW} --debug to print the debug information while executing the wasm ${NC}"
  exit 0
fi
if [[ $* == *--build* ]]; then
  echo -e "${YELLOW}Building the project ${NC}"
  ROOT_DIR=$(pwd)
  if [ ! -d "build" ]; then
    mkdir build
    mkdir build/output
  fi
  cd build
  if [ ! -d "openssl" ]; then
    git clone https://github.com/openssl/openssl.git -b openssl-3.2.1
    if [ ! -d "openssl" ]; then
      echo -e "${RED}Failed to clone the openssl repository${NC}"
      cd ..
      rm -rf build
      exit 1
    fi
    export CC=emcc
    export CXX=emcc
    cd openssl
    emconfigure ./Configure no-hw no-shared no-asm no-threads no-ssl3 no-dtls no-engine no-dso linux-x32 -static
    sed -i 's/$(CROSS_COMPILE)//' Makefile
    emmake make -j 16 build_generated libssl.a libcrypto.a apps/openssl
  fi
  if [[ $* == *--debug* ]]; then
    emcc -s WASM=1 -s ALLOW_MEMORY_GROWTH=1 -s MODULARIZE=1 -s EXPORT_NAME=OpenSSLWrapper --bind -I$ROOT_DIR/build/openssl/include -L$ROOT_DIR/build/openssl -lssl -lcrypto -o $ROOT_DIR/build/output/OpenSSLWrapper.js $ROOT_DIR/src/main.cpp -DDEBUG
  fi
  if [[ $* != *--debug* ]]; then
  emcc -s WASM=1 -s ALLOW_MEMORY_GROWTH=1 -s MODULARIZE=1 -s EXPORT_NAME=OpenSSLWrapper --bind -I$ROOT_DIR/build/openssl/include -L$ROOT_DIR/build/openssl -lssl -lcrypto -o $ROOT_DIR/build/output/OpenSSLWrapper.js $ROOT_DIR/src/main.cpp
  fi
  if [ ! -f $ROOT_DIR/build/output/OpenSSLWrapper.wasm ]; then
    echo -e "${RED}Failed to build the project${NC}"
    exit 1
  fi  
  if [ ! -f $ROOT_DIR/build/output/OpenSSLWrapper.js ]; then
    echo -e "${RED}Failed to build the project${NC}"
    exit 1
  fi
  echo -e "${GREEN} Build complete ${NC}"
  if [[ $* == *--skip-move* ]]; then
    echo -e "${YELLOW}Skipped moving the wasm file to the javascript directory${NC}"
    cd $ROOT_DIR/build/output
    echo -e "${YELLOW}You can find the wasm file in the directory${NC}"
    echo $(pwd)
    echo -e "${YELLOW}Size of the wasm file ${NC}"
    du -h OpenSSLWrapper.wasm
    echo -e "${YELLOW}Size of the javascript file ${NC}"
    du -h OpenSSLWrapper.js
  else
    echo -e "${YELLOW}Moving the wasm file to the javascript directory${NC}"
    mv $ROOT_DIR/build/output/OpenSSLWrapper.wasm $ROOT_DIR/../public/wasm/OpenSSLWrapper.wasm
    mv $ROOT_DIR/build/output/OpenSSLWrapper.js $ROOT_DIR/../resources/js/utils/OpenSSLWrapper.js
    echo -e "${YELLOW}You can find the js file in the directory${NC}"
    cd $ROOT_DIR/../resources/js/utils
    echo $(pwd)
    echo -e "${YELLOW}Size of the javascript file ${NC}"
    du -h OpenSSLWrapper.js
    sed -i 's/OpenSSLWrapper.wasm/\/wasm\/OpenSSLWrapper.wasm/g' OpenSSLWrapper.js
    echo "export { OpenSSLWrapper };" >> OpenSSLWrapper.js
    echo -e "${YELLOW}You can find the wasm file in the directory${NC}"
    cd $ROOT_DIR/../public/wasm
    echo $(pwd)
    echo -e "${YELLOW}Size of the wasm file ${NC}"
    du -h OpenSSLWrapper.wasm
    echo -e "${GREEN}Build complete${NC}"

  fi
fi
