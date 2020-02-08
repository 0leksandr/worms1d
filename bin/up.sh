#!/bin/sh
"$(dirname "$0")"/symfony.sh self:update -y
"$(dirname "$0")"/symfony.sh serve
