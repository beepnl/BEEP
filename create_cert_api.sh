#!/bin/sh

sudo /opt/bitnami/letsencrypt/scripts/generate-certificate.sh -m pim@iconize.nl -d api.beep.nl -d app.beep.nl -d graph.beep.nl -d help.beep.nl
