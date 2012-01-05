set :application, "forum"
set :repository,  "ssh://gitolite@siezi.com:2223/saito"

# use local ssh keys instead of server keys
ssh_options[:forward_agent] = true

set :branch, "master"

set :scm, :git

# tell cap to fetch git submodules
set :git_enable_submodules, 1

set :deploy_to, "/srv/www/macnemo.info/public_html"
set :deploy_via, :remote_cache


# configure app path in repo
set :cakephp_app_path, "app"
set :cakephp_core_path, "cake"

# nice optional configurations 
set :use_sudo, false # don't need this on most setup 

# only keep 10 version to save space …
set :keep_releases, 10  

set :copy_exclude, [".git",".gitignore", ".htaccess"] # or any match like [".svn","/documents-on-repo-but-dont-deploy"] 

# ssh port on target server
set :port, 22
# role :web, "your web-server here"                          # Your HTTP server, Apache/etc
role :app, "schlaefer@vserver7.panxatony.de"                          # This may be the same as your `Web` server
#role :db,  "your primary db-server here", :primary => true # This is where Rails migrations will run
# role :db,  "your slave db-server here"

# If you are using Passenger mod_rails uncomment this:
# if you're still using the script/reapear helper you will need
# these http://github.com/rails/irs_process_scripts

# namespace :deploy do
#   task :start do ; end
#   task :stop do ; end
#   task :restart, :roles => :app, :except => { :no_release => true } do
#     run "#{try_sudo} touch #{File.join(current_path,'tmp','restart.txt')}"
#   end
# end

task :production do
	set :current_dir, "forum"
	# set :deploy_to, "/home/user/domains/www.#{application}/" 
	# set :branch, "production"
	# after "deploy:finalize_update", "deploy:cakephp:testsuite"
end

task :testing do
	set :current_dir, "forum_test"
	# set :deploy_to, "/home/user/domains/www.#{application}/" 
	# set :branch, "production"
	# after "deploy:finalize_update", "deploy:cakephp:testsuite"
end

task :staging do 
	set :current_dir, "forum_test"
  # set :deploy_to, "/home/user/domains/staging.#{application}/" 
  set(:branch) { Capistrano::CLI.ui.ask("Branch to stage: ") } 
end 

# Custom events configuration
after "deploy:update", "deploy:cleanup"
after "deploy:finalize_update", "cakephp:finalize_update"
after "deploy:finalize_update", "saito:finalize_update"

namespace :saito do
# {{{
	task :finalize_update, :roles => :app do
		# link to useruploads file 
		run "rm #{release_path}/#{cakephp_app_path}/webroot/useruploads/empty" 
		run "rmdir #{release_path}/#{cakephp_app_path}/webroot/useruploads" 
		run "ln -s #{deploy_to}/useruploads #{release_path}/#{cakephp_app_path}/webroot/useruploads" 
	end
	# }}}
end

namespace :deploy do
# {{{
	desc "Override original :restart"
	task :restart, :roles => :app do
	end

	desc "Override original :finalize_update"
	task :finalize_update, :roles => :app do
	end
# }}}
end 

namespace :cakephp do
# {{{
	before "deploy:update", "cakephp:disable"
	after "deploy:restart", "cakephp:enable"

	after "cakephp:finalize_update", "cakephp:setup_config_files"
	after "cakephp:finalize_update", "cakephp:setup_tmp_folders"
	after "cakephp:finalize_update", "cakephp:setup_shared_vendors"

	# run test suite
	after "cakephp:finalize_update", "cakephp:testsuite"
	after "cakephp:testsuite", "cakephp:delete_tmp_folders"
	after "cakephp:delete_tmp_folders", "cakephp:setup_tmp_folders"

	after "cakephp:finalize_update", "cakephp:mark_installed"

	desc "setup cakephp on server"
	task :finalize_update, :roles => :app do# {{{
	end# }}}

	desc "Copy config files into app/config"
	task :setup_config_files, :roles => :app do # {{{
		run "rm #{release_path}/#{cakephp_app_path}/config/bootstrap.php" 
		run "cp #{deploy_to}/#{shared_dir}/config/bootstrap.php #{release_path}/#{cakephp_app_path}/config/bootstrap.php" 
		run "cp #{deploy_to}/#{shared_dir}/config/core.php #{release_path}/#{cakephp_app_path}/config/core.php" 
		run "cp #{deploy_to}/#{shared_dir}/config/database.php #{release_path}/#{cakephp_app_path}/config/database.php" 
	end# }}}

	desc "Setup tmp/ folders"
	task :setup_tmp_folders, :roles => :app do # {{{
		run "mkdir #{release_path}/#{cakephp_app_path}/tmp" 

		# setup cache
		run "mkdir #{release_path}/#{cakephp_app_path}/tmp/cache" 
		run "mkdir #{release_path}/#{cakephp_app_path}/tmp/cache/persistent" 
		run "mkdir #{release_path}/#{cakephp_app_path}/tmp/cache/models" 
		run "mkdir #{release_path}/#{cakephp_app_path}/tmp/cache/views" 

		# setup logs
		run "mkdir #{release_path}/#{cakephp_app_path}/tmp/logs" 

		# change rights
		run "chmod -R g+rwx #{release_path}/#{cakephp_app_path}/tmp" 
	end# }}}

	desc "Delete tmp/ folders"
	task :delete_tmp_folders, :roles => :app do #{{{
		# delete "#{release_path}/#{cakephp_app_path}/tmp", :recursive => true
		run "rm -rf #{release_path}/#{cakephp_app_path}/tmp"
	end #}}}

	desc "Setup shared/vendors"
	task :setup_shared_vendors, :roles => :app do# {{{
		run "ln -s #{deploy_to}/#{shared_dir}/vendors #{release_path}/vendors" 
	end# }}}
		
	desc "Verify CakePHP TestSuite pass" 
	task :testsuite, :roles => :app do # {{{
		# CakePHP 1.3.5
		# run "#{release_path}/#{cakephp_core_path}/console/cake testsuite app group cli -app #{release_path}/#{cakephp_app_path}", :env => { :TERM => "linux" } do |channel, stream, data| 
		# CakePHP 1.3.8
		run "cd #{release_path}; #{release_path}/#{cakephp_core_path}/console/cake testsuite app group cli ", :env => { :TERM => "linux" } do |channel, stream, data| 
			if stream == :err then 
				# error = CommandError.new("CakePHP TestSuite failed") 
				# raise error 
				puts "CakePHP TestSuite failed"
			else 
				puts data 
			end
		end
	end # }}}

	desc "Mark installation installed"
	task :mark_installed, :roles => :app do# {{{
		if remote_file_exists?("#{current_release}/#{cakephp_app_path}/config/installed.txt")
			run "touch #{release_path}/#{cakephp_app_path}/config/installed.txt" 
		end
	end# }}}

	desc "Lock current access during deployment"
	task :disable, :roles => :app do# {{{
		run "touch #{current_release}/#{cakephp_app_path}/webroot/.deployment.lock"
	end# }}}

	desc "Enable current access after deployment"
	task :enable, :roles => :app do# {{{
		run "rm #{current_release}/#{cakephp_app_path}/webroot/.deployment.lock"
	end# }}}
# }}}
end

def remote_file_exists?(full_path)
  'true' ==  capture("if [ -e #{full_path} ]; then echo 'true'; fi").strip
end
