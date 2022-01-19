## Prerequisites

- Apache with `mod_rewrite` enabled and `AllowOverride` set to `All` for Moodle directory
- Moodle, installed on root of http server
- PHP need to be version 7.* (8 will not work!)
- MySQL need to have privilege REFERENCES granted to Moodle user, besides usual ones
- `unzip` utility installed


## Running install script

- Copy it to any directory. Charon will be unpacked into `charon/` subdirectory there temporarily. It can be removed safely after install.
- Edit install script before running:
  - Set correct MOODLE directory
  - Set USER to match needed user:group for Apache
  - If needed, add additional steps into install script.
- Run it as root:
```sudo bash install.sh [-u] <charon>``` 
where
  - <charon> is zip file, containing installable Charon plugin. Something like `charon_1.2.3.zip`.
  - use flag -u for update, if older version is already installed


## Troubleshooting

If something goes wrong (most likely inside Moodle admin/cli/upgrade.php script), dont just run this script again! Installing Charon plugin is on half way and it need to be removed fully first, using Moodle Administrative tools.