#!/bin/sh

set -ev

# sudo apt-get install -y oracle-java8-set-default
# Hack to install Java SDK until the apt repo is updated with the latest
wget "http://javadl.oracle.com/webapps/download/AutoDL?BundleId=235717_2787e4a523244c269598db4e85c51e0c" -O java.tgz
tar zxpvf java.tgz
export JAVA_HOME=`pwd`/jre1.8.0_191
sudo mv /usr/bin/java /usr/bin/java1.7
sudo ln -s `pwd`/jre1.8.0_191/bin/java /usr/bin/java

curl -O https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-5.6.11.deb && sudo dpkg -i --force-confnew elasticsearch-5.6.11.deb
sudo service elasticsearch start
