# MakeFile for building all the docs at once.
# Inspired by the Makefile used by bazaar.
# http://bazaar.launchpad.net/~bzr-pqm/bzr/2.3/

PYTHON = python
ES_HOST =

.PHONY: all clean html website website-dirs

# Languages that can be built.
SOURCE_DIR = src

DEST = website

html:
	cd $(SOURCE_DIR) && make html

populate-index:
	php scripts/populate_search_index.php $(ES_HOST)


website: website-dirs html populate-index
	# Move HTML
	cp -r build/html $(DEST)

website-dirs:
	# Make the directory if its not there already.
	[ ! -d $(DEST) ] && mkdir $(DEST) || true

	# Make the downloads directory
	[ ! -d $(DEST)/_downloads ] && mkdir $(DEST)/_downloads || true

clean:
	rm -rf build/*

clean-website:
	rm -rf $(DEST)/*
