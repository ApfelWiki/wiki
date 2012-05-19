#!/bin/sh

# This scripts deploys the wiki to an ftp server.
#
# It doesn't delete files on the server, but overwrites them if necessary.
#
# You need to have ncftp installed.

###############################################################################
# Configuring FTP-Connection
###############################################################################

# server adress
SERVER='apfelwiki.de'
# working directory on the server
WORKDIR='awtest/'
# FTP-user
USER=''
# password for the ftp-user:
PASSWD=''

###############################################################################
# Files and directories to copy
###############################################################################

files[0]='index.php'
files[1]='pmwiki.php'
files[2]='robots.txt'
files[3]='favicon.ico'
files[4]='.htaccess'

directories[0]='cookbook'
directories[1]='local'
directories[2]='pub'
directories[3]='scripts'
directories[4]='wikilib.d'
directories[5]='wikirev.d'

wiki_d_files=(
  'Site.EditForm'
  )

###############################################################################
# Script Parameters
###############################################################################

# The wiki-root location relativ to the script-location
# SCRIPTREL='/..' means in a root subdir, e.g. docs/ 
SCRIPTREL='/..'

###############################################################################
# Script-Logic 
# 
# Don't change anything beyond that line
###############################################################################

# determine script location 
LOCALDIR="$( cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
LOCALDIR="$LOCALDIR$SCRIPTREL"

# upload files 
for file in ${files[@]}
	do

		fileLocation="$LOCALDIR/$file"

		if [ ! -f "$fileLocation" ]; then
   		echo "File $fileLocation doesn't exist. Aborting."
			exit 1;
		fi

		ncftpput -v -u $USER -p $PASSWD $SERVER "$WORKDIR" "$fileLocation"
	done		

# upload directories
for directory in ${directories[@]}
	do

		directoryLocation="$LOCALDIR/$directory"

		if [ ! -d "$directoryLocation" ];
		then
   		echo "Directory $directory doesn't exist. Aborting."
			exit 1;
		fi
		ncftpput -R -v -u $USER -p $PASSWD $SERVER "$WORKDIR" "$directoryLocation"
	done		

# upload wiki.d 
for file in ${wiki_d_files[@]}
	do

		fileLocation="$LOCALDIR/wiki.d/$file"

		if [ ! -f "$fileLocation" ]; then
   		echo "File $fileLocation doesn't exist. Aborting."
			exit 1;
		fi

		ncftpput -v -u $USER -p $PASSWD $SERVER "$WORKDIR/wiki.d/" "$fileLocation"
	done		

exit 0
