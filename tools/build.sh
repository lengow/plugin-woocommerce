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
        if [ -f "${DIRECTORY}/${FILE}" ]
        then
            find $DIRECTORY -name $FILE -nowarn -exec rm -rf {} \;
            echo -e "- Delete ${FILE} : ${VERT}DONE${NORMAL}"
        fi
        if [ -d "${DIRECTORY}/${FILE}" ]
        then
            rm -Rf ${DIRECTORY}/${FILE}
        fi
}

remove_directories(){
	DIRECTORY=$1
	find $DIRECTORY -maxdepth 1 -mindepth 1 -type d -exec rm -rf {} \;
	echo -e "- Delete $FILE : ${VERT}DONE${NORMAL}"
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
# Check parameters
if [ -z "$2" ]; then
	echo 'Deploy environment is not set: preprod or prod'
	echo
	exit 0
fi
if [ ! -z "$2" ] && [ "$2" == "preprod" ]; then
        ARCHIVE_NAME="preprod__${ARCHIVE_NAME}"        
fi

# Variables
FOLDER_TMP="/tmp/lengow-woocommerce"
FOLDER_LOGS="/tmp/lengow-woocommerce/logs"
FOLDER_CONFIG="/tmp/lengow-woocommerce/config"
FOLDER_EXPORT="/tmp/lengow-woocommerce/export"
FOLDER_TOOLS="/tmp/lengow-woocommerce/tools"
FOLDER_TRANSLATION="/tmp/lengow-woocommerce/translations/yml"

VERT="\e[32m"
ROUGE="\e[31m"
NORMAL="\e[39m"
BLEU="\e[36m"
DEPLOY_ENV=$2


# Process
echo
echo "#####################################################"
echo "##                                                 ##"
echo -e "##       "${BLEU}Lengow Magento${NORMAL}" - Build Module             ##"
echo "##                                                 ##"
echo "#####################################################"
echo
PWD=$(pwd)
FOLDER=$(dirname ${PWD})
echo $FOLDER
if [ ! -d "$FOLDER" ]; then
	echo -e "Folder doesn't exist : ${ROUGE}ERROR${NORMAL}"
	echo
	exit 0
fi
PHP=$(which php8.1)
echo ${PHP}

# Change config for preprod
if [ ! -z "${DEPLOY_ENV}" ] && [ "${DEPLOY_ENV}" == "preprod" ]; then
    sed -i 's/lengow.io/lengow.net/g' ${FOLDER}/includes/class-lengow-connector.php 
fi
if [ ! -z "${DEPLOY_ENV}" ] && [ "${DEPLOY_ENV}" == "prod" ]; then
    sed -i 's/lengow.net/lengow.io/g' ${FOLDER}/includes/class-lengow-connector.php 
fi


# Generate translations
${PHP} translate.php
echo -e "- Generate translations : ${VERT}DONE${NORMAL}"
# Create files checksum
${PHP} checkmd5.php
echo -e "- Create files checksum : ${VERT}DONE${NORMAL}"
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
echo -e "- Clean logs folder : ${VERT}DONE${NORMAL}"
# Clean export folder
remove_files $FOLDER_EXPORT "*.csv"
remove_files $FOLDER_EXPORT "*.yaml"
remove_files $FOLDER_EXPORT "*.json"
remove_files $FOLDER_EXPORT "*.xml"
echo -e "- Clean export folder : ${VERT}DONE${NORMAL}"
# Clean export folder
remove_directory $FOLDER_TOOLS
echo -e "- Remove Tools folder : ${VERT}DONE${NORMAL}"
#remove TMP FOLDER_TRANSLATION
remove_directory $FOLDER_TRANSLATION
echo -e "- Remove Translation yml folder : ${VERT}DONE${NORMAL}"

# Make zip
cd /tmp
zip -r $ARCHIVE_NAME "lengow-woocommerce"
echo -e "- Build archive : ${VERT}DONE${NORMAL}"
if [ -d  "~/Bureau" ]
then
    mv $ARCHIVE_NAME ~/Bureau
else 
    mv $ARCHIVE_NAME ~/shared
fi