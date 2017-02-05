SRC_DIR = ./src
BIN_DIR = ./bin
VENDOR_BIN = vendor/bin
check: cs
cs:
	$(VENDOR_BIN)/phpcs --standard=PSR2 -p -s $(SRC_DIR)
	$(VENDOR_BIN)/phpcs --standard=PSR2 -p -s $(BIN_DIR)
cbf:
	$(VENDOR_BIN)/phpcbf --standard=PSR2 $(SRC_DIR)
	$(VENDOR_BIN)/phpcbf --standard=PSR2 $(BIN_DIR)

