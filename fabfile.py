import os

from fabric.api import run, sudo, env, cd, local, prefix, put, lcd, settings
from fabric.contrib.project import rsync_project
from fabric.contrib.files import exists, sed

server_dir = '/home/project/live-server'
web_tmp_dir = '/home/project/live-server/tmp'
tmp_dir = '/tmp/live-server' + str(os.getpid()) + '/'


def _set_user_dir():
    global server_dir
    with settings(warn_only=True):
        issue = run('id root').lower()


def _prepare_local_website(install='true'):
    local('mkdir -p %s' % tmp_dir)
    local('cp -rv application vendor system resources tmp index.php %s' % tmp_dir)


def prepare_remote_dirs():
    _set_user_dir()
    if not exists(server_dir):
        sudo('mkdir -p %s' % server_dir)
        sudo('chmod -R 755 %s' % server_dir)
        sudo('chmod -R 777 %s' % web_tmp_dir)
        sudo('chown %s %s' % ('root', server_dir))


def chmod_tmp():
    run('chmod -R 777 %s' % web_tmp_dir)


def _clean_local_dir():
    local('rm -rf %s' % tmp_dir)


def host_type():
    run('uname -s')


def deploy(install='false'):
    _prepare_local_website(install)
    prepare_remote_dirs()
    rsync_project(local_dir=tmp_dir, remote_dir=server_dir, delete=False)
    chmod_tmp()
    _clean_local_dir()
