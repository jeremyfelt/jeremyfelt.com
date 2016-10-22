from fabric.api import local, settings, abort, run, cd, sudo, env
from fabric.contrib.console import confirm
from fabric.contrib.files import comment, uncomment

env.user = 'jeremyfelt'
env.hosts = ['jeremyfelt.com']
env.key_filename = '~/.ssh/foghlaim_rsa'

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
	local( "rsync -rvzh --delete --exclude '*.git*' www/wordpress/wp-content/themes/twentyfifteen/ content/themes/twentyfifteen/" )

def push_www():
	sudo( "chown -R jeremyfelt:jeremyfelt /tmp/www/jeremyfelt.com/www" )
	local("rsync -rvzh -e ssh --delete --exclude '.git' --exclude 'remote-config.php' --exclude 'local-config.php' www/ foghlaimeoir:/tmp/www/jeremyfelt.com/www" )
	sudo( "chown -R www-data:www-data /tmp/www/jeremyfelt.com/www" )
	sudo( "rsync -rvzh -e ssh --delete --exclude '.git' --exclude 'remote-config.php' --exclude 'local-config.php' /tmp/www/jeremyfelt.com/www/ /var/www/jeremyfelt.com/www", user="www-data" )

def push_tweets():
	sudo( "chown -R jeremyfelt:jeremyfelt /tmp/www/jeremyfelt.com/tweets" )
	local("rsync -rvzh -e ssh --delete --exclude '.git' tweets/ foghlaimeoir:/tmp/www/jeremyfelt.com/tweets" )
	sudo( "chown -R www-data:www-data /tmp/www/jeremyfelt.com/tweets" )
	sudo( "rsync -rvzh -e ssh --delete --exclude '.git' /tmp/www/jeremyfelt.com/tweets/ /var/www/jeremyfelt.com/tweets", user="www-data" )

def push_loop():
    sudo( "chown -R jeremyfelt:jeremyfelt /tmp/www/jeremyfelt.com/loopconf" )
    local("rsync -rvzh -e ssh --delete --exclude '.git' loopconf/ foghlaimeoir:/tmp/www/jeremyfelt.com/loopconf" )
    sudo( "chown -R www-data:www-data /tmp/www/jeremyfelt.com/loopconf" )
    sudo( "rsync -rvzh -e ssh --delete --exclude '.git' /tmp/www/jeremyfelt.com/loopconf/ /var/www/jeremyfelt.com/loopconf", user="www-data" )

def push_wceu():
    sudo( "mkdir -p /tmp/www/jeremyfelt.com/wceu-2016 && chown -R jeremyfelt:jeremyfelt /tmp/www/jeremyfelt.com/wceu-2016" )
    local("rsync -rvzh -e ssh --delete --exclude '.git' wceu-2016/ foghlaimeoir:/tmp/www/jeremyfelt.com/wceu-2016" )
    sudo( "chown -R www-data:www-data /tmp/www/jeremyfelt.com/wceu-2016" )
    sudo( "rsync -rvzh -e ssh --delete --exclude '.git' /tmp/www/jeremyfelt.com/wceu-2016/ /var/www/jeremyfelt.com/wceu-2016", user="www-data" )

def push_content():
	sudo( "chown -R jeremyfelt:jeremyfelt /tmp/www/jeremyfelt.com/content" )
	local( "rsync -rvzh -e ssh --delete --exclude '*.git*' --include '*/images/' --include '*/uploads/' --exclude 'uploads' --exclude 'images' content/ foghlaimeoir:/tmp/www/jeremyfelt.com/content" )
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
