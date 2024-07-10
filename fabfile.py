import os
from fabric import task, Connection
from invoke import run as local

# Define server details (modify as needed)
server_dir = '/home/project/live-server'
web_tmp_dir = '/home/project/live-server/tmp'
tmp_dir = '/tmp/live-server' + str(os.getpid()) + '/'


@task
def _set_user_dir(c):
    global server_dir
    with c.cd('/'):
        issue = c.run('id root', warn=True).stdout.lower()


@task
def _prepare_local_website(c):
    local(f'mkdir -p {tmp_dir}')
    local(
        f'cp -rv application system resources tmp index.php {tmp_dir}')


@task
def prepare_remote_dirs(c):
    _set_user_dir(c)
    if not c.run(f'test -d {server_dir}', warn=True).ok:
        c.sudo(f'mkdir -p {server_dir}')
    c.sudo(f'chmod -R 755 {server_dir}')

    if not c.run(f'test -d {web_tmp_dir}', warn=True).ok:
        c.sudo(f'mkdir -p {web_tmp_dir}')
    c.sudo(f'chmod -R 777 {web_tmp_dir}')

    c.sudo(f'chown -R ubuntu:ubuntu {server_dir}')


@task
def chmod_tmp(c):
    # Consider security implications (see note)
    c.run(f'chmod -R 777 {web_tmp_dir}')


@task
def _clean_local_dir(c):
    local(f'rm -rf {tmp_dir}')


@task
def host_type(c):
    c.run('uname -s')


@task
def deploy(c, install='false', username='ubuntu'):
    _prepare_local_website(c)
    prepare_remote_dirs(c)
    # Use the provided username
    local(f'rsync -avz -e "ssh" {tmp_dir} {username}@{c.host}:{server_dir}')
    chmod_tmp(c)
    _clean_local_dir(c)
