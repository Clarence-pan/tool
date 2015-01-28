#!/bin/sh
self=`realpath $0`
self=`dirname $self`
cd $self

sudo rsync -a --progress /work/tool /d/workspaces/ --exclude-from=/work/.rsync.exclude

