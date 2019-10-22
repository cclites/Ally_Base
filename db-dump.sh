dumpname=$(find ~/Downloads/ -type f -iname "*.sql")

echo 'dropping db...';
mysql -u homestead -p -h 192.168.10.10 -Nse 'drop database ally; create database ally';

echo 'loading database dump..';
mysql -u homestead -p -h 192.168.10.10 ally < $dumpname;

echo 'checking out master branch..';
git checkout master

echo 'pulling most recent code..';
git pull origin master

echo 'migrating database..';
php artisan migrate

echo 'running data scrubber..';
php artisan clear:sensitive_data demo --fix-only

# --fast