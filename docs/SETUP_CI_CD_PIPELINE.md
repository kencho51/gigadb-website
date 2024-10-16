# How to set up CI/CD pipelines on gitlab.com

Application development may involve implementing small code changes which are 
frequently checked into version control. Continuous Integration (CI) provides a 
consistent and automated way to build, package and test the application under 
development. Furthermore, Continuous Delivery (CD) automates the deployment of 
applications to specific infrastructure environments such as staging and 
production servers.

## Use of GitLab for Continuous Integration

GitLab provides a CI service used by GigaDB. The CI/CD pipeline is described in 
the [`.gitlab-ci.yml`](https://github.com/gigascience/gigadb-website/blob/develop/.gitlab-ci.yml)
file located at the root of the repository. A Runner in GitLab triggers the CI 
pipeline every time there is a code commit or push. GitLab.com allows you to use 
Shared Runners provided by GitLab Inc which are virtual machines running on 
GitLab's infrastructure to build any project.

The GigaDB `gitlab-ci.yml` configuration file tells the GitLab Runner to run a pipeline job 
with these stages:
* build for test
* test
* conformance and security
* production build
* staging deploy
* live deploy

That file is the entry point for configuring the GitLab pipelines. The configuration is organised in a modular way.
Thus, ``gitlab-ci.yml`` includes other configuration files to maintain a clear organisation:

```
ops/pipelines/
├── gigadb-build-jobs.yml #build jobs for CI and production go here
├── gigadb-conformance-security-jobs.yml #jobs that check for vulnerabilites and conformance to coding guidelines
├── gigadb-deploy-jobs.yml #jobs for deploying to production environments (staging and live)
├── gigadb-operations-jobs.yml #jobs for utilities and convenience for operating/debugging the pipelines
└── gigadb-test-jobs.yml #jobs for running tests as part of continuous integration
```

The above steps support testing and deployment of GigaDB, but assumes that the 
set up of the Docker server is already done separately.

### Mirroring your forked gigadb-website repository from GitHub

To begin, mirror your forked GitHub gigadb-website repository as a GitLab 
project. This is done by adding your GitHub gigadb-website repository to the 
GitLab Gigascience Forks organisation. To do this:

* Log into GitLab and go to the 
[gigascience/Forks page](https://gitlab.com/gigascience/forks).
 
* Click on *New Project* button in the top-right corner, then on the next screen click on *Run CI/CD for external repository* 

* Fill in the **Git repository URL** field, e.g. https://github.com/pli888/gigadb-website. 
* Check the Mirror repository checkbox and check Public visibility Level option. 
* Finally click the **Create project** button

### Understanding environments

Environmments are the foundation of the pipeline.
There are used in two contexts:
* When getting and setting variables
* When deploying the code 


#### When deploying the code

There are two types of environments: development and production.

| Environment name | Type | Purpose |
| --- | --- | --- |
| dev | development | a developer's local development machine where they create applications |
| CI | development | an environment created and hosted on GitLab to run automated tests continuously upon every commits (Continuous Integration) |
| staging | production | an environmment hosted on AWS cloud for final acceptance of a version of the web site product that's like the real live in every aspect |
| live | production | the real live web site product hosted on AWS cloud |

The local environment is on the developer's machine, that's what the dev environment refers to. 
The CI environment is implicitly created by the CI part of GitLab, that's where the code is deployed for the execution of the automated tests.
The CI is a gate-keeper for the production environments: deployment to staging and live can only happen if the tests pass in CI.

>**Note:** you will need to create **all** those environments in Gitlab dashboard under ``Operate > Environments``

#### Getting and settings variables on Gitlab

##### Environment attribute

A functionality of Gitlab is to store environment variables, so that we can use them in our deployed applications.
Because there is multiple deployment environments and the variables often differ from one to the other, 
Gitlab variables can be categoriseinto different environment which are:
* dev
* staging
* live
* All (or \*)

The environment is associated upon creation to each variable as one of its attributes.

>By convention the `staging` and `live` environments for variables are associated with the `staging` and `live` deployments respectively. 
>(i.e: a staging variable is only to be used on staging deployment environment, 
>and a live variable is to be used only on live deployment environment).
>`All` class of variables are needed in applications regardless of their deployment environments, 
>while the `dev` class of variables are equally used on a developer's local environments and on CI deployment environment.

Furthermore, variables have a hierarchal organisation that map to groups and projects.
So that, when there are variables is needed by all developers with the same value, such variable can be defined in a parent group, 
which allow sub-groups or sub-projects to access it without having to define it.

##### Group and projects 

The diagram below show the hierarchy we have in place.

```
Gigascience
├── Forks
│   ├── pli888-gigadb-website
│   └── rija-gigadb-website
│   └── ...
└── Upstream
    ├── alt-gigadb-website
    └── gigadb-website
```

All the leaves of the tree are _Gitlab projects_, the nodes are _Gitlab groups_.
The distinction is important as the Gitlab API endpoints are different.

>**Note:** This documentation is only concerned with variables and pipelines under the Forks group.
The Upstream group is for deployment to production environment and is out of scope for this documentation.

##### Gitlab token

We need token in order to interact with Gitlab API endpoints.
There are three types of authentication token we are interested in with respect to Gitlab authentication.

* Private token
* Group token
* Temporary token

The first one, private token, is one you create for yourself, You can interact with project variables.
If you are a core team member you can also interact with group variables.
If you are a contractor or contributor, you won't be able to access group variable.

The second one, group token, was generated by the core team to allow any bearer to access group variables.
This token is rotated regularly. If you are a contractor or contributor, that's the one you need.
You can ask the core team to have the latest one sent to you.

The last one, temporary token, is an ephemeral token created by Gitlab when a pipeline is run.
It is scoped for the duration of a pipeline jobs peformed on a Gitlab runner 
and provide authentication to API calls needed by jobs configuration.




### Configuring your GitLab gigadb-website project

Your new GitLab `gigadb-website` project requires configuration:

* The default branch needs to be selected for your project to allow you to 
perform CI/CD on this branch. Go to the Repository settings for your project, 
*e.g.*
[https://gitlab.com/gigascience/forks/pli888-gigadb-website/-/settings/repository],
 click on the *Expand* button next to the "Branch defaults" section header for the `Default Branch` settings. 
Use the drop-down menu to select the default branch and click the *Save changes* green 
button. Whatever branch you select requires a .gitlab-ci.yml file at the root of 
the repository project for CI/CD to work.

* Go to the CI/CD Settings for your project, *e.g.*
[https://gitlab.com/gigascience/forks/pli888-gigadb-website/-/settings/ci_cd]. In 
the *General pipelines* section, ensure that the *Public pipelines* checkbox is 
**NOT** ticked, otherwise variables will leak into the logs.
Click on the *Save changes* green button.
 
* The variables below need to be created for your project in the `Environment variables` 
section in the CI/CD Settings page.   
Make sure the "Protect variable" and "Expand variable reference" checkboxes are unchecked.
the Visibility radio input should be set to "Visible" except for the passwords and tokens that should be set to "Masked".

| Variable Name          | Value     | Environment |
|---|---|---|
| DOCKER_HUB_USERNAME    | Your login on Docker hub | All |
| DOCKER_HUB_PASSWORD    | Your password on Docker hub | All |
| COVERALLS_REPO_TOKEN   | Ask tech team | All |
| GIGADB_HOST | database | dev |
| GIGADB_USER | gigadb | dev |
| GIGADB_PASSWORD | Pick one | dev |
| GIGADB_DB | gigadb | dev |
| FUW_DB_HOST | database | dev |
| FUW_DB_USER | fuwdb | dev |
| FUW_DB_PASSWORD | Pick one | dev |
| FUW_DB_NAME |fuwdb |  dev |
| REVIEW_DB_HOST | reviewdb | dev |
| REVIEW_DB_USERNAME | reviewdb | dev |
| REVIEW_DB_PORT | 5432 | dev |
| REVIEW_DB_PASSWORD | Pick one | dev |
| REVIEW_DB_DATABASE | reviewdb | dev |
| GITLAB_PRIVATE_TOKEN | Ask tech team | All |

Those environment variables together with those in the Forks group are exported 
to the `.secrets` file and are listed 
[here](https://github.com/gigascience/gigadb-website/blob/develop/ops/configuration/variables/secrets-sample). 
All these GitLab CI/CD environment variables are referred to in the 
`gitlab-ci.yml` file or used in the CI/CD pipeline.


### Executing a Continuous Integration run
 
Your CI/CD pipeline can now be executed up to and including the **test** stage:

* Go to your pipelines page and click on *Run Pipeline*.

* In the *Create for* text field, confirm the name of the branch you want to run 
the CI/CD pipeline. The default branch should already be pre-selected for you. 
Then click on the *Create pipeline* button. 

* Refresh the pipelines page, you should see the CI/CD pipeline running. 
If the set up of your pipeline is successful, you will see it run the build, test, 
security and conformance stages defined in the `.gitlab-ci.yml` file.
 
## Continuous Deployment in the CI/CD pipeline

The deployment of `gigadb-website` code to staging and to live environments are all parts of the same pipeline described in the previous chapter.
While the jobs for continuous integration were performed in the stages up to the `test` stage, 
the deployment to staging and live environment are performed by the stages after that: 
for each environments there are two stages involved, the build stage and the deployment stage.

The jobs to deploy to the staging environment are fully automated and will trigger for every branch that are pushed to the Github remote.
The jobs for deploying to live environment are manually triggered and are enabled only for tags that are pushed to the Github remote.

The deployment of the code from the pipeline is dependent on the cloud infrastructure to be existing. 
So prior to this,  host machines have to be instantiated with a secure Docker daemon
on which the GigaDB application will be deployed. In addition, an RDS machine 
is created to provide a PostgreSQL database for GigaDB. Both these machines can
be used for a specific environment, most likely staging or live.

There are three pre-requisites to fulfill beforehand: 
* First, GitLab needs be configured for build and deployment to production (staging and live).
* Second, an AWS account need to be set up and elastic IP addresses created
* Third, several tools are needed to set up a Docker-enabled server on the AWS cloud: 
AWS-CLI, Terraform, and Ansible.

The rest of this document will guide you for the first requirement.
For the other requirements, and for guidance on infrastructure provisioning in general, do refer to the document:
[docs/SETUP_PROVISIONING.md](SETUP_PROVISIONING.md)


### Preparing GitLab for provisioning, build and deployment

``staging`` and ``live`` are what  matter in this section. If they are not already, these environments need to  created
in GitLab under the ``Deployments > Environments`` section.
They have two very important use:

 * the variables we need to configure the services and applications we deploy with need to be specific to each environment. GitLab allows
us to store variables and specify for which environment this variable is bound to (for organisation and security).
 * Gitlab stages and jobs must be tied to a specific environment, so that pipelines don't leak variables.


#### GitLab Variables

Ensure the following variables are set for their respective environments in the appropriate GitLab project.
Make sure the "Protect variable" and "Expand variable reference" checkboxes are unchecked.
the Visibility radio input should be set to "Visible" except for the passwords and tokens that should be set to "Masked".

| Name | value | 
| --- | --- |
| DEPLOYMENT_ENV | deployment environment goes here |
| REMOTE_HOME_URL | URL to the home website as https://yoursubodmain.gigadb.host |
| REMOTE_HOSTNAME | domain name associated to the elastic IP of the web server as yoursubdomain.gigadb.host |
| REMOTE_PUBLIC_HTTP_PORT | 80 |
| REMOTE_PUBLIC_HTTPS_PORT | 443 |
| REMOTE_SMTP_HOST | Pick an SMTP host |
| REMOTE_SMTP_PASSWORD | SMTP password |
| REMOTE_SMTP_PORT | 563 |
| REMOTE_SMTP_USERNAME | SMTP username |
| gigadb_db_host | keep empty, it will be overwritten by the provisioning script with RDS endpoint |
| gigadb_db_user | gigadb |
| gigadb_db_password | Pick a password |
| gigadb_db_database | gigadb |
| fuw_db_host | keep empty, it will be overwritten by the provisioning script with RDS endpoint |
| fuw_db_user | fuw |
| fuw_db_password | Pick a password |
| fuw_db_database | fuw |
| REVIEW_DB_DATABASE | reviewdb |
| REVIEW_DB_PASSWORD | Pick a password |
| REVIEW_DB_PORT | 5432 |
| REVIEW_DB_USERNAME | reviewdb |
| REVIEW_DB_HOST | reviewdb |
| PORTAINER_PASSWORD | Pick a password |
| remote_fileserver_hostname | files.yoursubdomain.gigadb.host | 

so, there should be 2 versions of each variable, one for each deployment environment (staging or live).

>**Notes:**The `yoursubdomain` part of `yoursubdomain.gigadb.host` needs to be replaced by string unique that indicates ownership environment.
E.g: `rm-staging.gigadb.host` or `peter-live.gigadb.host`
More info about domains can be found in the "Associate DNS records to EIPs for accessing endpoint on staging and on live servers" section of [docs/SETUP_PROVISIONING.md](SETUP_PROVISIONING.md)

#### examples:

| Key | Value | Masked | Environments |
| --- | --- | --- | --- |
| DEPLOYMENT_ENV | staging | x | staging|
| DEPLOYMENT_ENV | live | x | live|
| gigadb_db_password| 1234 | v | staging |
| gigadb_db_password| 5678 | v | live |
| PORTAINER_PASSWORD | "password for staging" | v | staging |
| PORTAINER_PASSWORD | "password for live" | v | live |

##### Bad examples:

| Key | Value | Masked | Environments |
| --- | --- | --- | --- |
| DEPLOYMENT_ENV | live | x | All (default) |
| gigadb_db_host | dockerhost | x | staging |

##### Variables for configuring PHP-FPM

Below are further variables that must be set in Gitlab variables.
They are necessary to configure PHP-FPM application server.


| name | value | environment |
| -- | -- | -- |
| PHP_APCU_MEMORY | 128M | staging |
| PHP_FPM_MAX_CHILDREN | 17 | staging |
| PHP_FPM_START_SERVERS | 4 | staging |
| PHP_FPM_MIN_SPARE_SERVERS | 4 | staging |
| PHP_FPM_MAX_SPARE_SERVERS | 12 | staging |
| PHP_CONN_LIMIT | disabled | staging |

##### Variables for credentials

The following variables need to be set for Environment "All (default)"

| Name | Masked? |
| --- | --- |
| DOCKER_HUB_USERNAME | no |
| DOCKER_HUB_PASSWORD | yes |
| AWS_ACCESS_KEY_ID | yes |
| AWS_SECRET_ACCESS_KEY | yes |

##### Optional

The following variables can be configured as Gitlab Variables (or in .env) like above
but as they already have default values, one needs to change their values only if want
to depart from the default.

| Key                 | Role                                      | Default on Dev/CI | Default on Staging | Default on Live |
|---------------------|-------------------------------------------|-------------------|--------------------|-----------------| 
| YII_DEBUG           | enable debug mode for extra logging       | true              | true               | false           |
| YII_TRACE_LEVEL     | how many lines of context for log entries | 3                 | 0                  | 0               | 
| DISABLE_CACHE       | whether to disable caching of DB queries  | false             | false              | false           |
| SEARCH_RESULT_LIMIT | Nb. of results per page                   | 10                | 10                 | 10              |

>**Note:** the value of each of the first three variables has impact on website performances. 
>The default values for the live environment offer the maximum performance. 
>While the default values for Dev/CI provide the most debugging information.

>**Note:** those three variables set the values for PHP constants of the same names that are
>defined in the Yii web application's ``index.php`` file 
>(generated from templates  ``ops/configuration/yii-conf/index.$GIGADB_ENV.php.dist``)

>**Note:** Although caching is on by default for all environments, 
>DISABLE_CACHE variable will still be available to provide flexibility if some specific development work needs it off.
>DISABLE_CACHE can be manually configured to true in .env to turn off caching in dev environment.


#### Jobs and stages in GitLab configuration files

Every job defined in the configuration need to have their stage and environment specified.
The former enables the execution order of the pipeline, and the latter ensures the variables for the selected 
environment only is made available to the pipeline's jobs.

>The name of valid stages to be used in GitLab configuration are listed at the top of the file ``.gitlab-ci.yml``  

>Ensure the value of ``environment:name:`` in GitLab configuration matches the environment that 
>you have created in Gitlab dashboard under ``Operate > Environments``


##### Examples:

 * from the ``ops/pipelines/gigadb-build-jobs.yml`` file:
```
build_live:
  variables:
    GIGADB_ENV: "live"
  extends: .pb_gigadb
  stage: production build
  environment:
    name: "live"
    deployment_tier: production
    url: $REMOTE_HOME_URL
```
 
 * from the ``.gitlab-ci.yml`` file:
```
sd_gigadb:
  variables:
    GIGADB_ENV: "staging"
  extends: .deploy
  stage: staging deploy
  environment:
    name: "staging"
    url: $REMOTE_HOME_URL
    on_stop: sd_teardown
```


>**Note:** Make sure you have a Docker Hub account and that its username and access token 
>(which can be created in Docker Hub's security settings)
>are used as value for GitLab variables DOCKER_HUB_USERNAME and DOCKER_HUB_PASSWORD 
>(set for the "All (default)" environment)
>as the ``before_script`` section of ``.gitlab-ci.yml`` uses them to login to Docker Hub 
>and pull the main base image to speed up the build stage

### Acceptance tests

the stage between staging and live in Gitlab pipeline is for acceptance tests.
The rationale is that nothing gets deployed to live if acceptance tests are failing.
Only when the acceptance tests are passing that the jobs in the live stage of the pipeline become actionable.

The following Gitlab variables are needed for the acceptance run, both in the pipeline but also locally

| name | value | environment |
| -- | -- | -- |
| SERVER_EMAIL | foo@bar.local | dev |
| AWS_ACCESS_KEY_ID | your access key to AWS | All |
| AWS_SECRET_ACCESS_KEY | your secret key to AWS | All |



