SRC_DIR = ./src
BIN_DIR = ./bin
VENDOR_BIN = vendor/bin
check: cs
cs:
	$(VENDOR_BIN)/phpcs --standard=phpcs-ruleset.xml -p -s $(SRC_DIR)
	$(VENDOR_BIN)/phpcs --standard=phpcs-ruleset.xml -p -s $(BIN_DIR)
cbf:
	$(VENDOR_BIN)/phpcbf --standard=phpcs-ruleset.xml $(SRC_DIR)
	$(VENDOR_BIN)/phpcbf --standard=phpcs-ruleset.xml $(BIN_DIR)

