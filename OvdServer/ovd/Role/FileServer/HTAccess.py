# Copyright (C) 2013 Ulteo SAS
# http://www.ulteo.com
# Author David LECHEVALIER <david@ulteo.com> 2013
#
# This program is free software; you can redistribute it and/or 
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; version 2
# of the License
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

import os

from ovd.Logger import Logger


class HTAccess:
	def __init__(self, path):
		self.path = os.path.join(path, ".htaccess")
		self.groups = []
	
	
	def addGroup(self, group):
		self.groups.append(group)
	
	
	def delGroup(self, group):
		if group in self.groups:
			self.groups.remove(group)
	
	
	def save(self):
		try:
			f = file(self.path, "w")
			if len(self.groups) == 0:
				f.write("deny from all")
			else:
				f.write("require group ")
			
			for group in self.groups:
				f.write(" %s"%(group))
			f.write("\n")
			f.close()
		
		except IOError, err:
			Logger.error("FS: unable to write .htaccess")
			Logger.debug("FS: unable to write .htaccess '%s' return: %s"%(path, str(err)))
			return False
		
		return True
