#!/bin/sh

sudo /opt/bitnami/ctlscript.sh stop apache
sudo /opt/bitnami/letsencrypt/lego --tls --email="pim@beep.nl" --domains="api.beep.nl" --domains="app.beep.nl" --domains="graph.beep.nl" --path="/opt/bitnami/letsencrypt" run
sudo /opt/bitnami/ctlscript.sh start apache