Servers and environment
=======================

Our unit of deployement are docker images for every microservices. 

Dev environment
---------------
The dev environment is described by the docker-compose file of the project

Production and other environments
------------------
Phase 1:
For ease of setup, monitoring and cost, we deploy everything on Google cloud run to start with. We use the following services

* Google cloud Run to run our services
* Google cloud SQL for the manage database
* Google cloud Build to build the images and deploy
* Google cloud logs for monitoring the application
* Gitlab to keep our code and trigger webooks for deployement

Phase 2:
While we are on Google cloud, we are also working on our own servers to 
own the full stack on our OVH virtual server. 