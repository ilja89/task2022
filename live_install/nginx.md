If using Nginx web server, server configuration rules for Moodle need to be complemented.

Following rules can be used. Please note, that `fastcgi`-related lines are depending on specific php configuration and may need checking or replacing. Generally, these lines should be same as used for running php for Moodle.


```
	## Charon
	location ~ "/mod/charon^/(.*)/$" {
		try_files $uri/ /mod/charon/$1$is_args$args;
	}

	location ~ "/mod/charon/" {
		try_files $uri $uri/ @charon_php;
	}

	location @charon_php {
		rewrite "/mod/charon/(.*)$" /mod/charon/index.php/$1 break;
		fastcgi_split_path_info  ^(.+\.php)(/.+)$;
		fastcgi_index            index.php;
		fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
		include fastcgi_params;
		fastcgi_param   PATH_INFO  $fastcgi_path_info;
		fastcgi_param   SCRIPT_FILENAME $document_root$fastcgi_script_name;
	}

	location ^~ /mod/charon/plugin/storage/logs/ {
		deny all;
		return 403;
	}
	##
```
