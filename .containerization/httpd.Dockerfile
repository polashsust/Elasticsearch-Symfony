FROM httpd:2.4-alpine
LABEL maintainer="marcus.haase@milchundzucker.de" \
      containermode="production"
COPY ./.containerization/httpd.conf /usr/local/apache2/conf/httpd.conf
EXPOSE 80
