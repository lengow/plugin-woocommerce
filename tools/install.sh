#!/bin/bash
MAGE=$1

copy_directory(){
	ORIGINAL_DIRECTORY="$(dirname "$(pwd)")"
	DESTINATION_DIRECTORY="$MAGE$1/wp-content/plugins/lengow"
	if [ -d "$ORIGINAL_DIRECTORY" ]; then
		if [ -e "$DESTINATION_DIRECTORY" ]; then
			unlink $DESTINATION_DIRECTORY
		fi
		ln -s $ORIGINAL_DIRECTORY $DESTINATION_DIRECTORY
		echo "✔ Create directory : $DESTINATION_DIRECTORY"
	else
		echo "⚠ Missing directory : $ORIGINAL_DIRECTORY"
	fi
	return $TRUE
}

copy_directory "/"

exit 0;
