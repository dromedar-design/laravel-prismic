<?php

namespace DromedarDesign\Prismic\Database;

class Connection
{
    public function prepare($query)
    {
        return new Statement($query);
    }

    public function exec($query)
    {
        $statement = $this->prepare($query);

        $statement->execute();

        return $statement->rowCount();
    }
}
