Files and directories in the repository root directory
======================================================

readme.txt

    This file.

functions-documentation.txt

    File documenting the behaviour of certain functions found in
    hf-brass/common.php.

Data dictionary/

    This contains the CREATE TABLE statements and CREATE PROCEDURE statements
    needed to create the database objects used by the site. I have put these
    here so that they can be referred to rather than so that they can be
    edited. If you want a database object altered then you should bring it to
    my attention what you are doing, since you need me to put your changes
    into effect.

brass/

    This contains the PHP and JS scripts that are found in the
    brass.orderofthehammer.com directory (publicly accessible scripts). The
    file _std-include.php is also found in this directory, but because
    different versions exist for the production and test sites, in the
    repository it is in different-files/.

hf-brass/

    This contains various PHP includes that are located in a directory
    outside of webroot. The file _config.php is also found in this directory,
    but because different versions exist for the production and test sites,
    in the repository it is in different-files/.

different-files/

    Contains the following:
        - the maintenance scripts, which differ between the test and
          production sites
        - a file, "Maintenance Summary.txt", that lists the tasks done by each
          of the maintenance scripts and says when they run
        - each version of the files _std-include.php (found in webroot) and
          _config.php (found in the includes directory).