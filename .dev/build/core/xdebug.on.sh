docker exec -ti ks_core bash -c "apt-get install -y iproute2"
docker exec -ti ks_core bash -c "xdebugon.sh"
docker exec -ti ks_core bash -c "service php-fpm restart"
