#!/bin/bash

a2dismod mpm_event || true
a2dismod mpm_worker || true
a2enmod mpm_prefork || true

exec apache2-foreground
