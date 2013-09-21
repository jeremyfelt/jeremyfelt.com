from fabric.api import local, settings, abort, run, cd, sudo, env
from fabric.contrib.console import confirm
from fabric.contrib.files import comment, uncomment

env.hosts = ['jeremyfelt.com']
env.path = '/srv/web/jeremyfelt.com'
env.path_config = '/srv/web/jeremyfelt.com/config'
env.stats_dir = '/srv/web/stats.foghlaim.com'

def pull_plugins():
	local( "rsync -rvzh -e ssh --delete --exclude '*.git*' jeremyfelt@jeremyfelt.com:/srv/web/jeremyfelt.com/content/plugins/ content/plugins/" )

def pull_themes():
	local( "rsync -rvzh -e ssh --delete --exclude '*.git*' jeremyfelt@jeremyfelt.com:/srv/web/jeremyfelt.com/content/themes/ content/themes/" )

def pull_uploads():
	local( "rsync -rvzh -e ssh --delete --exclude '*.git*' jeremyfelt@jeremyfelt.com:/srv/web/jeremyfelt.com/content/uploads/ content/uploads/" )
	local( "rsync -rvzh -e ssh --delete --exclude '*.git*' jeremyfelt@jeremyfelt.com:/srv/web/jeremyfelt.com/content/images/ content/images/" )

def pull_content():
	pull_plugins()
	pull_themes()
	pull_uploads()

def push_web():
	sudo( "chown -R jeremyfelt:jeremyfelt /srv/web/jeremyfelt.com/www" )
	local("rsync -rvzh -e ssh --delete --exclude '.git' --exclude 'local-config.php' www/ jeremyfelt@jeremyfelt.com:/srv/web/jeremyfelt.com/www" )
	sudo( "chown -R www-data:www-data /srv/web/jeremyfelt.com/www" )

def push_tweets():
	sudo( "chown -R jeremyfelt:jeremyfelt /srv/web/jeremyfelt.com/tweets" )
	local("rsync -rvzh -e ssh --delete --exclude '.git' tweets/ jeremyfelt@jeremyfelt.com:/srv/web/jeremyfelt.com/tweets" )
	sudo( "chown -R www-data:www-data /srv/web/jeremyfelt.com/tweets" )

def push_content():
	sudo( "chown -R jeremyfelt:jeremyfelt /srv/web/jeremyfelt.com/content" )
	local("rsync -rvzh -e ssh --delete --exclude '*.git*' --include '*/images/' --include '*/uploads/' --exclude 'uploads' --exclude 'images' content/ jeremyfelt@jeremyfelt.com:/srv/web/jeremyfelt.com/content" )
	sudo( "chown -R www-data:www-data /srv/web/jeremyfelt.com/content" )

def push_stats():
	with settings(warn_only=True):
		if run( "test -d %(stats_dir)s" % env ).failed:
			run( "mkdir %(stats_dir)s" % env )
	sudo( "chown -R jeremyfelt:jeremyfelt %(stats_dir)s" % env )
	local( "rsync -rvzh -e ssh --delete --exclude '*.git*' stats/ jeremyfelt@jeremyfelt.com:%(stats_dir)s/" % env )
	sudo( "chown -R www-data:www-data %(stats_dir)s" % env)

def push_config():
	with settings(warn_only=True):
		if run("test -d %(path_config)s" % env ).failed:
			run( "mkdir %(path_config)s" % env )
	local( "rsync -rv -e ssh config/nginx-config/*.conf jeremyfelt@jeremyfelt.com:%(path_config)s/" % env )
	comment( "%(path_config)s/nginx.conf" % env, r'^.*sendfile off;.*$' );
	uncomment( "%(path_config)s/nginx.conf" % env, r'^.*sendfile on;.*$' );
	comment( "%(path_config)s/jeremyfelt.com.conf" % env, r'^.*fastcgi_read_timeout 3600s;.*$' );
	uncomment( "%(path_config)s/jeremyfelt.com.conf" % env, r'^.*fastcgi_read_timeout 30s;.*$' );
	sudo( "cp %(path_config)s/jeremyfelt.com.conf /etc/nginx/conf.d/jeremyfelt.com.conf" % env )
	sudo( "cp %(path_config)s/stats.foghlaim.com.conf /etc/nginx/conf.d/stats.foghlaim.com.conf" % env )
	sudo( "cp %(path_config)s/nginx.conf /etc/nginx/nginx.conf" % env )

def restart_services():
	sudo( "service nginx restart" )
	sudo( "service php5-fpm restart" )
	sudo( "service memcached restart" )

def deploy():
	push_config()
	push_web()
	push_tweets()
	push_content()
	restart_services()
