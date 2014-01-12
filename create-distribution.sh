#!/bin/sh
#
#    InstallApp, Software for Kloxo
#
#    Copyright (C) 2000-2009     LxLabs
#    Copyright (C) 2009-2010     LxCenter
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU Affero General Public License as
#    published by the Free Software Foundation, either version 3 of the
#    License, or (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU Affero General Public License for more details.
#
#    You should have received a copy of the GNU Affero General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#########################################################
#    Create distribution packages for InstallApp software.
#
black='\E[30;47m'
red='\E[31;47m'
green='\E[32;47m'
yellow='\E[33;47m'
blue='\E[34;47m'
magenta='\E[35;47m'
cyan='\E[36;47m'
white='\E[37;47m'

alias Reset="tput sgr0"      #  Reset text attributes to normal
                             #+ without clearing screen.
Reset

cecho ()                     # Color-echo.
                             # Argument $1 = message
                             # Argument $2 = color
{
local default_msg="No message passed."
                             # Doesn't really need to be a local variable.

message=${1:-$default_msg}   # Defaults to default message.
color=${2:-$black}           # Defaults to black, if not specified.

  echo -e "$color"
  echo "$message"
  Reset                      # Reset to normal.

  return
}  

########################
for i in $(ls -d */);
	do
	ZIPNAME="${i%%/}.zip"
	DIRNAME="${i%%/}/"
 if [ ! $DIRNAME == "installappdata/" ]; then
	 if [ -f $ZIPNAME ]
	  then
	   cecho " Deleting: $ZIPNAME" $red
	  rm -f $ZIPNAME
	 fi
	cecho " Packing: $DIRNAME (can take a while)" $blue
	zip -r9q $ZIPNAME $DIRNAME -x "*/.svn/*"
 fi
done

	cd installappdata
        cecho " Packing: installappdata (can take a while)" $blue
	zip -r9q installappdata.zip ./ ../lx_template.servervars.phps -x "*/.svn/*" ".svn/*"
	cd ..
	mv installappdata/installappdata.zip .

cecho " ###############" $blue
cecho " Packaging Done." $blue
cecho " ###############" $blue
echo ""
echo ""

