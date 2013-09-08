# -*- mode: ruby -*-
# vi: set ft=ruby :

dir = Dir.pwd

Vagrant.configure("2") do |config|

  # Configurations from 1.0.x can be placed in Vagrant 1.1.x specs like the following.
  config.vm.provider :virtualbox do |v|
    v.customize ["modifyvm", :id, "--memory", 512]
  end

  config.ssh.forward_agent = true
  
  config.vm.box = "precise32"
  config.vm.box_url = "http://files.vagrantup.com/precise32.box"
  config.vm.hostname = "jeremyfelt"
  config.vm.network :private_network, ip: "10.10.12.13"

  if defined? VagrantPlugins::HostsUpdater
    config.hostsupdater.aliases = [
              "dev.jeremyfelt.com",
      "dev.content.jeremyfelt.com",
       "dev.tweets.jeremyfelt.com"
    ]
  end

  config.vm.synced_folder "database/", "/srv/database"
  config.vm.synced_folder "database/data/", "/var/lib/mysql", :mount_options => [ "dmode=777", "fmode=777" ]

  config.vm.synced_folder "www",     "/srv/web/jeremyfelt.com/www",     :owner => "www-data", :mount_options => [ "dmode=775", "fmode=774" ]
  config.vm.synced_folder "content", "/srv/web/jeremyfelt.com/content", :owner => "www-data", :mount_options => [ "dmode=775", "fmode=774" ]
  config.vm.synced_folder "tweets",  "/srv/web/jeremyfelt.com/tweets",  :owner => "www-data", :mount_options => [ "dmode=775", "fmode=774" ]

  config.vm.provision :shell, :path => File.join( "provision", "provision.sh" )

end
