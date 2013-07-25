<?php
namespace Instafeed;

use \Instafeed\Adapter\Instagram;


class Tag extends Instagram
{
    /**
     * Retrieve recent posts that match passed tag
     *
     * @param string $name tag to filter on
     * @return string|bool
     */
    public function recent($name)
    {
        $this->resource = '/v1/tags/' . trim((string)$name) . '/media/recent';
        $this->request(self::METHOD_GET);

        if ($this->responseCode == 200) {
            return $this->response;
        }

        return false;
    }
}
