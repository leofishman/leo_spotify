# **Drupal Challenge (2023)**

This is a plain vanilla Drupal 10 installation pre-configured for use with DDEV.

Features pre-configured / pre-installed:

* DDEV yaml file.
* Needed Docker containers (Webserver, MySQL, PHP8, NodeJS).
* Vanilla Drupal 10.
* Drush.
* Composer.
* SCSS pre-processors.

## **Challenge Instructions**

You can find the [challenge instructions here](https://git.jobsity.com/jobsity/drupal-challenge-updated/-/wikis/Drupal-Challenge-Instructions).

## **Setup instructions**

### Step #1: DDEV environment setup

**This is a one time setup - skip this if you already have a working DDEV environment.**

Follow [DDEV environment setup instructions](https://ddev.readthedocs.io/en/stable/users/install/).

Recommendation: on MacOS and Windows, enable the [mutagen](https://ddev.readthedocs.io/en/stable/users/install/performance/) filesystem for a better performance.

### Step #2: Cloning repo

1. Create a user at [https://git.jobsity.com](https://git.jobsity.com).
2. Open this repository, and fork it using the button at the top.
3. Within your fork, go to Settings/General/"Visibility, project features, permissions" and set the Project visibility to Private.

### Step #3: Project setup

1. Clone your forked repo into your Projects directory.

   ```plaintext
   git clone https://git.jobsity.com/jobsity/drupal-challenge-update jobsity-drupal-challenge
   cd jobsity-drupal-challenge
   ```
2. Initialize the DDEV site.

   This will initialize local settings and spin up docker containers.

   ```plaintext
   ddev start
   ```

   You might be asked to introduce your sudo password.
3. Download project dependencies.

   This will download Drupal core and other needed dependencies.

   ```plaintext
   ddev composer install
   ```
4. Install Drupal website.

   This will install a plain Drupal instance dropping all the tables on the database.

   ```plaintext
   ddev drush site:install --account-name=admin --account-pass=admin -y
   ```
5. Install SCSS node modules (optional).

   If you would like to use SCSS for your styling, install Node dependencies for gulp compiler.

   ```plaintext
   ddev ssh
   cd web/themes/custom/dc
   npm install --location-global gulp-cli
   exit
   ```

   For watching SCSS changes and compiling to CSS run `ddev composer theme:watch`.\
   For compiling SCSS to CSS run `ddev composer theme:build`.
6. Open Drupal website and log in.

   At this point, you should be able to access the working Drupal website at [https://drupal-challenge-updated.ddev.site/](https://drupal-challenge-updated.ddev.site/) or by running `ddev launch`.\
   Use `admin` for the username and `admin` for the password to log in as an administrator or by running `ddev drush uli`.

### Step #4 - Start coding

Tips:

* Provide ALL information, step by step, on the root README file for how to install from scratch your Drupal Challenge using DDEV. Try to follow your own steps on a new clone to ensure it works. These will be the same steps the evaluator will follow. When creating another clone of the repo, before starting ddev, change the `.ddev/config.yaml` name attribute to avoid collision names.
* Use all Drupal best practices you know, the evaluator will take them into account. Add standard Drupal comments and feel free to comment on your own custom code when you think it is worth it or adds value.
* Create a clean structure architecture in Drupal showcasing your knowledge of site-building to store and relate the different pieces of data you will retrieve from the API and the proper use of Drupal entities. If you need to install any contributed module, install it by using composer. Put any custom code into a custom module.
* Provide steps for the evaluator on how to ingest content into the website. Think about which cases is it better to ingest data and when to consume data in real-time from the API. Do not send your database; the structure, configuration, and content must be imported.
* Provide all the information for the evaluator on how to configure the API credentials or what steps need to follow to make the API connection to work.
* Provide a good, cohesive front-end with proper theming to demonstrate your knowledge of working with Twigs templates, use of CSS and responsiveness, Javascript, hooks and preprocessors, and other Drupal 8/9/10 functionality. Put all your theming code, assets, styles, and templates into the provided dc theme.
* For some challenge feature solutions, you might need to be creative. The challenge instructions are crystal clear. In case you have doubts or questions, take your own assumptions. Document them on the README file to let the evaluator know your decisions.