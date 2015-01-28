#!/bin/sh
self=`realpath $0`
self=`dirname $self`
cd $self

rsync -a --progress /d/workspaces/tool /work/ --exclude-from=/work/.rsync.exclude

