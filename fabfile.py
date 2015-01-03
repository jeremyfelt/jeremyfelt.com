from fabric.api import local, settings, abort, run, cd, sudo, env
from fabric.contrib.console import confirm
from fabric.contrib.files import comment, uncomment

env.hosts = ['jeremyfelt.com']
env.key_filename = '~/.ssh/foghlaimeoir'

def initial_sync():
    run( "mkdir -p /tmp/www/jeremyfelt.com")
    local( "rsync -rvzh -e ssh --delete --exclude '.git' ./ foghlaimeoir:/tmp/www/jeremyfelt.com" )

def pull_plugins():
	local( "rsync -rvzh -e ssh --delete --exclude '*.git*' foghlaimeoir:/var/www/jeremyfelt.com/content/plugins/ content/plugins/" )

def pull_themes():
	local( "rsync -rvzh -e ssh --delete --exclude '*.git*' foghlaimeoir:/var/www/jeremyfelt.com/content/themes/ content/themes/" )

def pull_uploads():
	local( "rsync -rvzh -e ssh --delete --exclude '*.git*' foghlaimeoir:/var/www/jeremyfelt.com/content/uploads/ content/uploads/" )
	local( "rsync -rvzh -e ssh --delete --exclude '*.git*' foghlaimeoir:/var/www/jeremyfelt.com/content/images/ content/images/" )

def pull_content():
	pull_plugins()
	pull_themes()
	pull_uploads()

def sync_themes():
	local( "rsync -rvzh --delete --exclude '*.git*' www/wordpress/wp-content/themes/twentyfourteen/ content/themes/twentyfourteen/" )
	local( "rsync -rvzh --delete --exclude '*.git*' www/wordpress/wp-content/themes/twentythirteen/ content/themes/twentythirteen/" )

def push_www():
	local("rsync -rvzh -e ssh --delete --exclude '.git' --exclude 'remote-config.php' --exclude 'local-config.php' www/ foghlaimeoir:/tmp/www/jeremyfelt.com/www" )
	sudo( "chown -R www-data:www-data /tmp/www/jeremyfelt.com/www" )
	sudo( "rsync -rvzh -e ssh --delete --exclude '.git' --exclude 'remote-config.php' --exclude 'local-config.php' /tmp/www/jeremyfelt.com/www/ /var/www/jeremyfelt.com/www", user="www-data" )

def push_tweets():
	local("rsync -rvzh -e ssh --delete --exclude '.git' tweets/ foghlaimeoir:/tmp/www/jeremyfelt.com/tweets" )
	sudo( "chown -R www-data:www-data /tmp/www/jeremyfelt.com/tweets" )
	sudo( "rsync -rvzh -e ssh --delete --exclude '.git' /tmp/www/jeremyfelt.com/tweets/ /var/www/jeremyfelt.com/tweets", user="www-data" )

def push_content():
	local("rsync -rvzh -e ssh --delete --exclude '*.git*' --include '*/images/' --include '*/uploads/' --exclude 'uploads' --exclude 'images' content/ foghlaimeoir:/tmp/www/jeremyfelt.com/content" )
	sudo( "chown -R www-data:www-data /tmp/www/jeremyfelt.com/content" )
	sudo( "rsync -rvzh -e ssh --delete --exclude '*.git*' --include '*/images/' --include '*/uploads/' --exclude 'uploads' --exclude 'images' /tmp/www/jeremyfelt.com/content/ /var/www/jeremyfelt.com/content", user="www-data" )

def restart_services():
	sudo( "service nginx restart" )
	sudo( "service php5-fpm restart" )
	sudo( "service memcached restart" )

def push_all():
	push_www()
	push_tweets()
	push_content()
	restart_services()
