#! /usr/bin/python

import os, sys, pysvn

path = os.path.dirname( os.path.realpath( __file__ ) )

# Detect the version
if os.environ.has_key("OVD_VERSION"):
	version = os.environ["OVD_VERSION"]
else:
	c = pysvn.Client()
	revision = c.info(path)["revision"].number
	version = "99.99~trunk+svn%05d"%(revision)


# OS detection
if sys.platform == "linux2":
	platform_dir = "Linux"
elif sys.platform == "win32":
	platform_dir = "Windows"
else:
	raise Exception("No supported platform")


f = file(os.path.join(path, "setup.py.in"), "r")
content = f.read()
f.close()

content = content.replace("@VERSION@", str(version))

f = file(os.path.join(path, "setup.py"), "w")
f.write(content)
f.close()
