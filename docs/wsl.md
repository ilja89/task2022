# Developing Charon in Windows using WSL

## Prerequisites

- Windows version, able to run WSL2
https://docs.microsoft.com/en-us/windows/wsl/install
- Docker desktop installed (check README.md)
https://docs.docker.com/desktop/windows/install/

### Cleaning up old containers

If you have set up Charon before, old Charon-related containers, images and volumes should be removed before installing Charon again.


## Install Linux in WSL

https://docs.microsoft.com/en-us/windows/wsl/install

for example:

```bash
wsl --install -d Ubuntu
```
You can choice any available Linux distribution to install.

Remember user name and password, you did set on install!


## Get Charon

Charon should be cloned into your home catalog. Run in Linux terminal:

```bash
cd
git clone https://gitlab.cs.ttu.ee/ained/charon.git
```
Log in with your user credentials


## Configure Docker Desktop in Windows

- Settings/General: make sure, that WSL2 is enabled
- Settings/Resources/WSL Integration: enable integration with your Linux distro


## Create docker containers in Linux terminal (Install Moodle)

```bash
cd ~/charon/docs
sudo docker-compose up -d
```
Password will be asked. Use this one, you did set during Linux setup.
If `docker-compose` is not found, restart Docker Desktop in Windows and try again.

You can follow process of installing Moodle same way, as described in main README.md file.


## Install Charon

Follow instructions in README.md file, starting from `Install Charon` subitem


## Accessing Charon source code

Charon code can be accessed from any Windows program (incl PhpStorm), 
using path like `\\wsl$\Ubuntu\home\user\charon`, 
where `Ubuntu` is name of your Linux distribution and `user` is your username, choicen on Linux setup.

