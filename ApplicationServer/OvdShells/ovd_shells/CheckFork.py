# -*- coding: utf-8 -*-

# Copyright (C) 2009,2010 Ulteo SAS
# http://www.ulteo.com
# Author Julien LANGLOIS <julien@ulteo.com> 2009, 2010
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

import glob
import md5
import os
import random
import time

from Module import Module
import Platform

class CheckFork(Module):
	def __init__(self, application):
		Module.__init__(self, application)
		self.token = md5.md5("%f%f"%(random.random(), time.time())).hexdigest()
	
	def beforeStartApp(self):
		os.environ["OVD_INSTANCE_TOKEN"] = self.token
	
	
	def afterStartApp(self):
		p = self.findAProcess()
		
		while p is not None:
			time.sleep(1)
			
			p = self.findAProcess()
	

	def findAProcess(self):
		buf = "OVD_INSTANCE_TOKEN=%s"%(self.token)
		
		return Platform.findProcessWithEnviron(buf)
