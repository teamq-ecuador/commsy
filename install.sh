#!/usr/bin/env bash



cd /var/www/html/
sudo -H -u www-data bash -c 'php composer.phar install'
sudo -H -u www-data bash -c 'php bin/console doctrine:fixtures:load --append'
sudo -H -u www-data bash -c 'php bin/console --no-interaction doctrine:migrations:migrate'
sudo -H -u www-data bash -c 'php bin/console fos:elastica:populate'
sudo -H -u www-data bash -c 'yarn install'
sudo -H -u www-data bash -c 'yarn run encore dev'
rm -r var/cache/dev/
chown www-data:root /var/www