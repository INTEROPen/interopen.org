# interopen.org

## Contributing

### Workflow for updating the site

1. Get a clean and up-to-date copy of the website from the GitHub repository:

    https://github.com/INTEROPen/interopen.org.git

2. Copy the `wp-config-sample.php` to `wp-config.php` and update with local settings.

3. Make changes to the site on your local machine

4. Commit and push them back to GitHub (on the master branch)

5. If you have permission, to deploy to the live site:

    * Connect to the live web server via FTP
    * Copy your local copy of the files to the `/site/wwwroot` directory


### Rewrite Rules
If you want to add a rewrite rule for the web server, you need to add it to both the .htaccess file, and the web.config file.

Azure Hosting will use the web.config file, but Apache-based servers will use .htaccess.

This is a test by Atif
