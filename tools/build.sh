#!/bin/bash
# Build archive for Woocommerce plugin
# Step :
#     - Remove .DS_Store
#     - Remove .README.md
#     - Remove .idea
#     - Clean export folder
#     - Clean logs folder
#     - Clean translation folder
#     - Remove tools folder
#     - Remove .git Folder and .gitignore

remove_if_exist(){
	if [ -f $1 ]; then
		rm $1
	fi
}

remove_directory(){
	if [ -d "$1" ]; then
		rm -rf $1
	fi
}
remove_files(){
	DIRECTORY=$1
	FILE=$2
	find $DIRECTORY -name $FILE -nowarn -exec rm -rf {} \;
	echo "- Delete $FILE : ""$VERT""DONE""$NORMAL"""
}

remove_directories(){
	DIRECTORY=$1
	find $DIRECTORY -maxdepth 1 -mindepth 1 -type d -exec rm -rf {} \;
	echo "- Delete $FILE : ""$VERT""DONE""$NORMAL"""
}
# Check parameters
if [ -z "$1" ]; then
	echo 'Version parameter is not set'
	echo
	exit 0
else
	VERSION="$1"
	ARCHIVE_NAME='lengow.woocommerce.'$VERSION'.zip'
fi

# Variables
FOLDER_TMP="/tmp/lengow-woocommerce"
FOLDER_LOGS="/tmp/lengow-woocommerce/logs"
FOLDER_CONFIG="/tmp/lengow-woocommerce/config"
FOLDER_EXPORT="/tmp/lengow-woocommerce/export"
FOLDER_TOOLS="/tmp/lengow-woocommerce/tools"
FOLDER_TRANSLATION="/tmp/lengow-woocommerce/translations/yml"

VERT="\\033[1;32m"
ROUGE="\\033[1;31m"
NORMAL="\\033[0;39m"
BLEU="\\033[1;36m"

# Process
echo
echo "#####################################################"
echo "##                                                 ##"
echo "##       ""$BLEU""Lengow Woocommerce""$NORMAL"" - Build Module          ##"
echo "##                                                 ##"
echo "#####################################################"
echo
FOLDER="$(dirname "$(pwd)")"
echo $FOLDER
if [ ! -d "$FOLDER" ]; then
	echo "Folder doesn't exist : ""$ROUGE""ERROR""$NORMAL"""
	echo
	exit 0
fi

# Generate translations
php translate.php
echo "- Generate translations : ""$VERT""DONE""$NORMAL"""
# Create files checksum
php checkmd5.php
echo "- Create files checksum : ""$VERT""DONE""$NORMAL"""
#remove TMP FOLDER
remove_directory $FOLDER_TMP
#copy files
cp -rRp $FOLDER $FOLDER_TMP
# Remove dod
remove_files $FOLDER_TMP "dod.md"
# Remove Readme
remove_files $FOLDER_TMP "README.md"
# Remove CHANGELOG
remove_files $FOLDER_TMP "CHANGELOG"
# Remove .gitignore
remove_files $FOLDER_TMP ".gitignore"
# Remove .git
remove_files $FOLDER_TMP ".git"
# Remove .DS_Store
remove_files $FOLDER_TMP ".DS_Store"
# Remove .idea
remove_files $FOLDER_TMP ".idea"
# Remove Jenkinsfile
remove_files $FOLDER_TMP "Jenkinsfile"
# Clean Config Folder
remove_files $FOLDER_CONFIG "marketplaces.json"
# Clean Log Folder
remove_files $FOLDER_LOGS "*.txt"
echo "- Clean logs folder : ""$VERT""DONE""$NORMAL"""
# Clean export folder
remove_files $FOLDER_EXPORT "*.csv"
remove_files $FOLDER_EXPORT "*.yaml"
remove_files $FOLDER_EXPORT "*.json"
remove_files $FOLDER_EXPORT "*.xml"
echo "- Clean export folder : ""$VERT""DONE""$NORMAL"""
# Clean export folder
remove_directory $FOLDER_TOOLS
echo "- Remove Tools folder : ""$VERT""DONE""$NORMAL"""
#remove TMP FOLDER_TRANSLATION
remove_directory $FOLDER_TRANSLATION
echo "- Remove Translation yml folder : ""$VERT""DONE""$NORMAL"""

# Make zip
cd /tmp
zip "-r" $ARCHIVE_NAME "lengow-woocommerce"
echo "- Build archive : ""$VERT""DONE""$NORMAL"""
mv $ARCHIVE_NAME ~/Bureau