<?php

return [
    'imagePath' => '/var/images',

    /**
     * Defines that the album name is in the defined deptch of the path. This is useful if the albums have
     * nested directories.
     */
    'staticAlbumNameDepth' => 1,

    /**
     * Whether the static album depth feature is enabled or not. Enable this, if your folder structure contains
     * a fix path structure, such as /my/custom/depth/ALBUMNAME/subfolder/x/y
     *
     * Define the location of ALBUNAME by setting the deptch in staticAlbumNameDepth
     */
    'staticAlbumDepthEnabled' => true,
];
