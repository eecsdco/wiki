EECS Wiki Server
================

MediaWiki farm manager customized for the EECS environment. Developed for the Department of
Electrical Engineering and Computer Science at the University of Michigan.

Notice
------

This project is still under development and should not be used in a live environment. At best, 
this could be considered an alpha release. It's a bit hacked together in places.

Features
--------

- Cosign integration
- Group based permissions
- Administrator control panel for each wiki instance
- Automated deployment of new wiki instances
- Automated upgrades of each wiki instance


Requirements
------------

- Apache setup and working
- mySQL setup and working with known root username and password (enter into root/global/config.php)
- Cosign module setup and working with Apache SSL server
- Directy structure as outlined below

Directory Structure
-------------------

```
root								the root directory of the apache SSL server
	global	
		config.php					rename the stock config.dist.php and enter your database info
		cosign						contains the cosign scripts
		data						each wiki needs an entry here (created automatically)
			WIKI-NAME					contains the wiki images, uploads, and cache
		management					contains the main wiki management class
		mediawiki					contains MediaWiki source
			1.17						contains MediaWiki 1.17 source
			1.18						contains MediaWiki 1.18 source
			current --> 1.17			symlink to "current" mediawiki version
			beta --> 1.18				symlink to "test" mediawiki version
			offline
			upgrade						created automatically during an upgrade operation
		stats
	instances 						each wiki needs an entry here (created automatically)
		WIKI-NAME --> ../global/mediawiki/current					
	manage							contains the end-user management area
```
	
Permissions
-----------

Apache must have R+W+E permission (7) on the following directories and files.

- root/global/data (and everything below)
- root/global/mediawiki
- root/global/mediawiki/current
- root/global/mediawiki/beta
- root/global/mediawiki/offline
- root/global/mediawiki/upgrade
- root/instances (and everything below)

Custom Files Within MediaWiki Source
------------------------------------

In order to work within the EECS environment, the following files must exist in the MediaWiki source
for each version. If you add a new version (i.e. 1.19 or above) then you must copy them from older
directory (i.e. 1.18). 

```
mediawiki/LocalSettings.php						configures MediaWiki at runtime (db, extensions)
mediawiki/extensions/eecs_environment.php		configured MediaWiki to work in EECS environment
mediawiki/extensions/*							all other extensions listed at the bottom of 
												LocalSettings.php
```
												
The goal of this project was to NOT modify MediaWiki in any way that cannot be done with 
extension hookse or API calls (i.e. all changes are made in LocalSettings.php or in an extension).
Because of this, it should be (relatively) easy to upgrade to each new version of MediaWiki. 

However, the MediaWiki source may change over time. You should check the developer documentation
before upgrading instances to a new version of MediaWiki. 

- Developer Docs: http://www.mediawiki.org/wiki/Manual:Contents
- Extension Hook Docs: http://www.mediawiki.org/wiki/Manual:Hooks (used in eecs_environment.php extension)
												
Apache Configuration
--------------------

The EECS wiki server uses apache in SSL only. However, both the *:80 and *:443 servers must have
their document roots set to the same location. You must setup your ssl.conf as shown in the 
included ssl.conf file.

For the sake of clarity, here is the required section of the ssl.conf file.

```
<VirtualHost _default_:443>

...

<IfModule mod_cosign.c>

	# Configure CoSign
	CosignProtected						Off
	CosignHostname						weblogin.umich.edu
    CosignRedirect                      https://weblogin.umich.edu/
    CosignPostErrorRedirect             https://weblogin.umich.edu/cosign/post_error.html
    CosignService                       eecs-wiki
    CosignCrypto                        /var/cosign/certs/wiki.key /var/cosign/certs/wiki.cert /var/cosign/certs/CA
    CosignValidReference                ^https?:\/\/.*\.umich\.edu(/.*)?
    CosignValidationErrorRedirect       http://weblogin.umich.edu/cosign/validation_error.html

    # Setup cosign filter manager
	<Location /cosign/valid>
        SetHandler						cosign
	    CosignProtected     			Off
	    Allow from all
        Satisfy any
    </Location>

	<Directory /y/wiki/global>
	    Options -Indexes
	</Directory>

	# Rewrite wikis from the root to make things easier
	<Directory /y/wiki>
	    Options +FollowSymlinks
        RewriteEngine On
        RewriteCond %{REQUEST_URI} !^/(instances|global|manage|cosign|phpmyadmin)
	    RewriteCond %{REQUEST_FILENAME} !-f
	    RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ /instances/$1/ [L,QSA,PT,DPI]
	</Directory>

	<Directory /y/wiki/data>
	    Options +Indexes
	    AllowOverride					All
        CosignProtected					Off
	</Directory>

	# Optional. Needed if you want to use phpmyadmin in the root directory.
	<Directory /y/wiki/phpmyadmin>
	    AllowOverride					All
	    CosignProtected					On
	    Authtype						Cosign
	    CosignAllowPublicAccess			Off
	    require user mcolf nlderbin rwcohn
	</Directory>

	# Force login for management area
	<Directory /y/wiki/manage>
	    AllowOverride					All
	    CosignProtected					On
	    Authtype						Cosign
	    CosignAllowPublicAccess			Off
	</Directory>

	# Allow public access to wikis by default
	<Directory /y/wiki/instances>
	    AllowOverride					All
	    CosignProtected					On
	    Authtype						Cosign
	    CosignAllowPublicAccess     	On
	</Directory>

	# Setup forced login directory
	<Directory /y/wiki/global/cosign>
	    AllowOverride               	All
        CosignProtected             	On
        Authtype                    	Cosign
	    CosignAllowPublicAccess     	Off
	</Directory>	

	# Allow UM access to stats 
	<Directory /y/wiki/global/stats>
	    CosignProtected					On
	    Authtype						Cosign
	    CosignAllowPublicAccess			On
	    <Files index.php>
			require valid-user	    
	    </Files>
	</Directory>

</IfModule>

</VirtualHost>
```

Legal
-----

Copyright (c) 2011, Matt Colf

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted, provided that the above
copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
