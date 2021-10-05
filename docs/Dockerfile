FROM bitnami/moodle:3.9.0

RUN apt-get update -y \
    && apt-get upgrade -y \
    && curl -sL https://deb.nodesource.com/setup_12.x | bash - \
    && apt-get install -y nodejs build-essential libpng-dev git \
    && npm config set registry "https://registry.npmjs.org"
