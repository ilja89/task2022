FROM bitnami/moodle:3.9.0

RUN curl -sL https://deb.nodesource.com/setup_12.x | bash - \
    && apt-get install -y nodejs \
    && npm config set registry "http://registry.npmjs.org"
