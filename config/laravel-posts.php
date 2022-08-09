<?php

return [
    /*
     * The name of the column which holds the ID of the model related to the reviews.
     *
     * Only change this value if you have set a different name in the migration for the reviews table.
     */
    'model_primary_key_attribute' => 'model_id',

    /*
     * The table name where your posts will be stored.
     */
    'posts_table_name' => 'dk_posts',

    /*
     * The column name where posts slug should be generated from
     */
    'generate_slug_from' => 'title',

     /*
     * The column name where slugs should be saved to
     */
    'save_slug_to' => 'slug',

];
