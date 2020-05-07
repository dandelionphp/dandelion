# Dandelion - A tool for managing PHP projects with multiple packages. 
![Travis Build](https://travis-ci.org/dandelionphp/dandelion.svg?branch=master, "")
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dandelionphp/dandelion/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dandelionphp/dandelion/?branch=master)

## Getting started
Set up a repository which includes all your packages source code and the dandelion configuration file. 

Example structure:  
```
.  
+-- dandelion.json  
+-- Package1  
|   +-- ...  
+-- Package2    
|   +-- ...  
+-- tmp
```
  
Next you want to create read-only repositories for your packages in order to split and release into those repositories.
The read-only repositories will be the one you link to in your dandelion configuration file.
  
Create a temp folder in your repository root where the split repositories will be located. You can set this directory to any path you like in the configuration file. 

## Usage
The recommended way is to use docker. The docker image comes with a self-contained splitsh binary, you don't need to download or compile it yourself.
If you choose one of the other options to run Dandelion, please have a look at the section "Install splitsh".
  
Choose your favorite method below.

### Steps
1. Run `dandelion init`, this will initialize your project and add all repository remotes.
2. Run `dandelion split` to push your changes to remote branches.
3. Run `dandelion release` to create a new release for your packages.

### Docker
`docker run --rm -v $PWD:/home/dandelion dandelion:latest -c $COMMAND` 

When you choose Docker as preferred way, you will need to use a Git authentication flow that runs without user interaction. 
If that is not a viable option for you but you still want to use Docker you might configure your own Docker image using a keypair authentication flow.  
  
In general this is only necessary for private repositories. If you use Dandelion in a public repository context you can use https.

### PHAR
Download the latest release from the [Github](https://github.com/dandelionphp/dandelion/releases).

`php dandelion.phar $COMMAND`

### composer
#### Global Installation
`composer global require dandelionphp/dandelion`

Make sure to add composer to your PATH:  
**macOS:** $HOME/.composer/vendor/bin
**GNU / Linux Distributions:** $HOME/.config/composer/vendor/bin or $HOME/.composer/vendor/bin

`dandelion $COMMAND`

#### Local Installation
`composer require --dev dandelionphp/dandelion`

`vendor/bin/dandelion $COMMAND`

### List all available commands
`dandelion list`

### Install splitsh
TBD

## Example Config
```json
{
  "repositories": {
    "dandelion-example-1": {
      "url": "https://<GITHUB_TOKEN>@github.com/<OWNER>/<REPO>.git",
      "path": "example-1",
      "version": "1.1.0"
    },
    "dandelion-example-2": {
      "url": "https://<GITHUB_TOKEN>@github.com/<OWNER>/<REPO>.git",
      "path": "example-2",
      "version": "1.0.0"
    }
  },
  "pathToTempDirectory": "/tmp/",
  "pathToSplitshLite": "/usr/bin/splitsh-lite"
}
```
_Note: It is important to use trailing slashes on path references_

### Git Authentication
#### Github
If you choose to use the token based authentication, keep in mind that your token will be visible in plain text in your dandelion config as well as 
in `.git/config`. You'll find more information about that auth style [here](https://github.blog/2012-09-21-easier-builds-and-deployments-using-git-over-https-and-oauth/).
Go to your [Github](https://github.com/settings/tokens) settings and create a personal access token. The scope for the access token is `repo`.

