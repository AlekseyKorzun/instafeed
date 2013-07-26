<?php
namespace Instafeed;

use \Instafeed\Adapter\Instagram;


class Tag extends Instagram
{
    /**
     * Retrieve information about a specific tag
     *
     * @param string $name tag to retrieve information for
     * @return string|bool
     */
    public function info($name)
    {
        $this->resource = '/v1/tags/' . trim((string)$name);
        $this->request(self::METHOD_GET);

        if ($this->responseCode == 200) {
            return $this->response;
        }

        return false;
    }

    /**
     * Search for a specific tag
     *
     * @param string $name tag to search for
     * @return string|bool
     */
    public function search($name)
    {
        $this->resource = '/v1/tags/search';
        $this->request = array(
            'q' => trim((string) $name)
        );
        $this->request(self::METHOD_GET);

        if ($this->responseCode == 200) {
            return $this->response;
        }

        return false;
    }


    /**
     * Retrieve recent posts that match passed tag
     *
     * @param string $name tag to filter on
     * @param int $minimum minimum media id to retrieve
     * @param int $maximum maximum media id to retrieve
     * @return string|bool
     */
    public function recent($name, $minimum = null, $maximum = null)
    {
        $this->resource = '/v1/tags/' . trim((string)$name) . '/media/recent';

        if (!is_null($minimum) || !is_null($maximum)) {
            $this->request = array();

            if (!is_null($minimum)) {
                $this->request['min_id'] = (int) $minimum;
            }

            if (!is_null($maximum)) {
                $this->request['max_id'] = (int) $maximum;
            }
        }

        $this->request(self::METHOD_GET);

        if ($this->responseCode == 200) {
            return $this->response;
        }

        return false;
    }
}
