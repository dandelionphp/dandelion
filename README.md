# Dandelion PHP

![Travis Build](https://travis-ci.org/dandelionphp/dandelion.svg?branch=master, "")

Dandelion is a wrapper for the amazing Git Split Tree utility [https://github.com/splitsh/lite](splitch-lite). splitch-lite allows you to split a monolithic repository into multiple read-only repositories. Dandelion allows you to use splitch-lite through a phar file and adds a neat configuration management on top of it.

## Getting started
Set up a repository which includes all your packages source code and the dandelion configuration file. 

Example structure:  
.  
+-- dandelion.json  
+-- Package1  
|   +-- ...  
+-- Package2    
|   +-- ...  
+-- tmp  
  
Next you want to create read-only repositories for your packages in order to split and release into those repositories.
The read-only repositories will be the one you link to in your dandelion configuration file.
  
Create a temp folder in your repository root where the splitted repositories will be located. You can set this directory to any path you like in the configuration file. 

## Usage

### Preferred usage Docker
`docker run -v $PWD:/home/dandelion -c "dandelion split:all master"`  
`docker run -v $PWD:/home/dandelion -c "dandelion release:all master 1.0.0"`

### Using PHAR
Download the latest release from the [Github](https://github.com/dandelionphp/dandelion/releases).

`php dandelion.phar split:all master`  
`php dandelion.phar release:all master 1.0.0`

## Example Config
```json
{
    "repositories": {
        "dandelion-example-1": {
            "url": "https://<GITHUB_USERNAME>:<GITHUB_TOKEN>@github.com:dandelionphp/dandelion-example-1.git",
            "path": "example-1"
        },
        "dandelion-example-2": {
            "url": "https://<GITHUB_USERNAME>:<GITHUB_TOKEN>@github.com:dandelionphp/dandelion-example-2.git",
            "path": "example-2"
        }
    },
    "pathToTempDirectory": "tmp/"
}
```