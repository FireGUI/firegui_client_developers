<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2018, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author    EllisLab Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright    Copyright (c) 2014 - 2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license    http://opensource.org/licenses/MIT    MIT License
 * @link    https://codeigniter.com
 * @since    Version 2.0
 * @filesource
 */
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * CodeIgniter File Caching Class
 *
 * @package        CodeIgniter
 * @subpackage    Libraries
 * @category    Core
 * @author        EllisLab Dev Team
 * @link
 */
class MY_Cache_file extends CI_Driver
{

    /**
     * Directory in which to save cache files
     *
     * @var string
     */
    protected $_cache_path;
    const TAGS_CACHE_FILE = 'tags_mapping.json';
    const CACHE_TIME = 300;

    private $entityTagsCache = [];


    /**
     * Initialize file-based cache
     *
     * @return    void
     */
    public function __construct()
    {
        $CI = &get_instance();
        $CI->load->helper('file');
        $path = $CI->config->item('cache_path');
        $this->_cache_path = ($path === '') ? APPPATH . 'cache/' : $path;

        if (defined('CACHE_TIME')) {
            $this->CACHE_TIME = CACHE_TIME;
        } else {
            $this->CACHE_TIME = self::CACHE_TIME;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Fetch from cache
     *
     * @param    string    $id    Cache ID
     * @return    mixed    Data on success, FALSE on failure
     */
    public function get($id)
    {
        $data = $this->_get($id);
        return is_array($data) ? $data['data'] : false;
    }

    // ------------------------------------------------------------------------

    /**
     * Save into cache
     *
     * @param    string    $id    Cache ID
     * @param    mixed    $data    Data to store
     * @param    int    $ttl    Time to live in seconds
     * @param    bool    $raw    Whether to store the raw value (unused)
     * @return    bool    TRUE on success, FALSE on failure
     */
    public function save($id, $data, $ttl = 60, $tags = [])
    {
        $contents = array(
            'time' => time(),
            'ttl' => $ttl,
            'data' => $data,
        );

        $folder = dirname($this->_cache_path . $id);
        if (!is_dir($folder)) {
            mkdir($folder, DIR_WRITE_MODE, true);
        }

        if ($result = write_file($this->_cache_path . $id, serialize($contents))) {
            @chmod($this->_cache_path . $id, 0640);
            $this->saveTagsMapping($id, $tags, time());
            return true;
        } else {
            debug($result);
            debug($this->_cache_path . $id, true);
        }

        return false;
    }

    public function saveTagsMapping($id, $tags = [], $timestamp)
    {
        $mapping = $this->getTagsMapping();

        if (!$tags) {
            $tags = [];
        }
        foreach ($tags as $tag) {
            if (!array_key_exists($tag, $mapping)) {
                $mapping[$tag] = [];
            }
            $mapping[$tag][$id] = $timestamp;
        }
        $this->writeMappingFile($mapping);
    }
    public function writeMappingFile($mapping)
    {
        if (write_file($this->_cache_path . self::TAGS_CACHE_FILE, json_encode($mapping, JSON_PRETTY_PRINT))) {
            return true;
        } else {
            return false;
        }
    }
    public function getTagsMapping()
    {
        $mapping_file = $this->_cache_path . self::TAGS_CACHE_FILE;

        if (file_exists($mapping_file)) {
            $content = file_get_contents($mapping_file);
            $mapping = json_decode($content, true);

            // Verifica se il JSON decodificato è un array
            if (!is_array($mapping)) {
                // Se non è un array valido, restituisci un array vuoto e ricrea il file
                $this->writeMappingFile([]);
                return [];
            }

            // Rimuovi i file scaduti
            $current_time = time();
            $updated = false;

            foreach ($mapping as $tag => &$files) {
                if (!is_array($files)) {
                    // Se $files non è un array, rimuovi questo tag
                    unset($mapping[$tag]);
                    $updated = true;
                    continue;
                }

                foreach ($files as $file => $timestamp) {
                    if (!is_numeric($timestamp) || ($current_time - $timestamp) > self::CACHE_TIME) {
                        unset($files[$file]);
                        @unlink($this->_cache_path . $file);
                        $updated = true;
                    }
                }

                if (empty($files)) {
                    unset($mapping[$tag]);
                    $updated = true;
                }
            }

            // Riscrivi il file solo se ci sono stati cambiamenti
            if ($updated) {
                $this->writeMappingFile($mapping);
            }

            return $mapping;
        }

        return [];
    }

    // ------------------------------------------------------------------------

    /**
     * Delete from Cache
     *
     * @param    mixed    unique identifier of item in cache
     * @return    bool    true on success/false on failure
     */
    public function delete($id)
    {

        return is_file($this->_cache_path . $id) ? @unlink($this->_cache_path . $id) : false;
    }

    /**
     * Delete from Cache all data related to tags
     *
     * @param    array    tags
     * @return    bool    true on success/false on failure
     */
    public function deleteByTags(array $tags)
    {
        foreach ($tags as $tag) {
            $this->deleteByTag($tag);
        }
    }
    public function deleteByTag($tag)
    {
        $mapping = $this->getTagsMapping();
        if (array_key_exists($tag, $mapping)) {
            foreach ($mapping[$tag] as $id => $timestamp) {
                $this->delete($id);
            }
            unset($mapping[$tag]);
            $this->writeMappingFile($mapping);
        }
    }


    // ------------------------------------------------------------------------

    /**
     * Increment a raw value
     *
     * @param    string    $id    Cache ID
     * @param    int    $offset    Step/value to add
     * @return    New value on success, FALSE on failure
     */
    public function increment($id, $offset = 1)
    {
        $data = $this->_get($id);

        if ($data === false) {
            $data = array('data' => 0, 'ttl' => 60);
        } elseif (!is_int($data['data'])) {
            return false;
        }

        $new_value = $data['data'] + $offset;
        return $this->save($id, $new_value, $data['ttl'])
            ? $new_value
            : false;
    }

    // ------------------------------------------------------------------------

    /**
     * Decrement a raw value
     *
     * @param    string    $id    Cache ID
     * @param    int    $offset    Step/value to reduce by
     * @return    New value on success, FALSE on failure
     */
    public function decrement($id, $offset = 1)
    {
        $data = $this->_get($id);

        if ($data === false) {
            $data = array('data' => 0, 'ttl' => 60);
        } elseif (!is_int($data['data'])) {
            return false;
        }

        $new_value = $data['data'] - $offset;
        return $this->save($id, $new_value, $data['ttl'])
            ? $new_value
            : false;
    }

    // ------------------------------------------------------------------------

    /**
     * Clean the Cache
     *
     * @return    bool    false on failure/true on success
     */
    public function clean(array $exclude_files = [])
    {

        return delete_files($this->_cache_path, false, true, 0, $exclude_files);
    }

    // ------------------------------------------------------------------------

    /**
     * Cache Info
     *
     * Not supported by file-based caching
     *
     * @param    string    user/filehits
     * @return    mixed    FALSE
     */
    public function cache_info($type = null)
    {
        return get_dir_file_info($this->_cache_path);
    }

    // ------------------------------------------------------------------------

    /**
     * Get Cache Metadata
     *
     * @param    mixed    key to get cache metadata on
     * @return    mixed    FALSE on failure, array on success.
     */
    public function get_metadata($id)
    {
        if (!is_file($this->_cache_path . $id)) {
            return false;
        }

        $data = unserialize(file_get_contents($this->_cache_path . $id));

        if (is_array($data)) {
            $mtime = filemtime($this->_cache_path . $id);

            if (!isset($data['ttl'], $data['time'])) {
                return false;
            }

            return array(
                'expire' => $data['time'] + $data['ttl'],
                'mtime' => $mtime,
            );
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Is supported
     *
     * In the file driver, check to see that the cache directory is indeed writable
     *
     * @return    bool
     */
    public function is_supported()
    {
        return is_really_writable($this->_cache_path);
    }

    // ------------------------------------------------------------------------

    /**
     * Get all data
     *
     * Internal method to get all the relevant data about a cache item
     *
     * @param    string    $id    Cache ID
     * @return    mixed    Data array on success or FALSE on failure
     */
    protected function _get($id)
    {

        if (!is_readable($this->_cache_path . $id)) {
            return false;
        }

        $data = @unserialize(file_get_contents($this->_cache_path . $id));

        if (!$data || ($data['ttl'] > 0 && time() > $data['time'] + $data['ttl'])) {

            @unlink($this->_cache_path . $id);
            return false;
        }

        return $data;
    }

    public function getCacheAdapter()
    {
        if (!$adapter = $this->_adapter) {
            $filename = APPPATH . 'cache/cache-controller';
            $defaultAdapter = array('adapter' => 'dummy'); //Default adapter dummy to disable cache by default
            if (!file_exists($filename)) {
                @file_put_contents_and_create_dir($filename, serialize($defaultAdapter), LOCK_EX);
                $this->_adapter = $defaultAdapter;
                return $defaultAdapter;
            }

            $controllerFileContents = file_get_contents($filename);
            $adapter = @unserialize($controllerFileContents);
            $this->_adapter = $adapter;
            if (!is_array($adapter) or !array_key_exists('adapter', $adapter)) {
                $this->_adapter = $defaultAdapter;
                return $defaultAdapter;
            }
        }

        return $adapter;
    }

    /**
     * Enable/Disable for the caching system
     * @param bool $enable
     * @return bool Booleano indicante successo/fallimento dell'operazione
     */
    public function toggleCachingSystem($enable = true)
    {
        if (!$enable) {
            $adapter = ['adapter' => 'dummy'];
        } elseif (!empty($this->mycache->apc) && $this->mycache->apc->is_supported()) {
            $adapter = ['adapter' => 'apc', 'backup' => 'file'];
        } else {
            $adapter = ['adapter' => 'file', 'backup' => 'dummy'];
        }

        $out = file_put_contents(APPPATH . 'cache/cache-controller', serialize($adapter), LOCK_EX);
        return $out !== false;
    }

    /**
     * Check cache abilitata o meno
     * @return type
     */
    public function isCacheEnabled()
    {
        $adapter = $this->getCacheAdapter();

        return ($adapter['adapter'] !== 'dummy');
    }

    public function clearCache($drop_template_files = false, $key = null)
    {
        $cache_paths = [
            'sql' => APPPATH . 'cache/sql',
            'fullpages' => APPPATH . 'cache/fullpages',
            'database_schema' => APPPATH . 'cache/database_schema',
            'apilib' => APPPATH . 'cache/apilib',
        ];

        $cache_controller_file = APPPATH . 'cache/cache-controller';
        $cache_config_file = APPPATH . 'cache/cache-config.json';
        $tags_mapping_file = $this->_cache_path . self::TAGS_CACHE_FILE;

        if ($key) {
            if ($key == 'raw_queries') {
                $key = 'sql';
            }
            if ($key == 'full_page') {
                $key = 'fullpages';
            }

            if (isset($cache_paths[$key])) {
                $this->removeDirectory($cache_paths[$key]);
            } else {
                @unlink(APPPATH . 'cache/' . $key);
            }
        } else {
            // Preserve cache controller and config
            $cache_controller_content = @file_get_contents($cache_controller_file);
            $cache_config_content = @file_get_contents($cache_config_file);

            // Clear all cache directories
            foreach ($cache_paths as $path) {
                $this->removeDirectory($path);
            }

            // Clear tags_mapping.json file
            if (file_exists($tags_mapping_file)) {
                @unlink($tags_mapping_file);
            }

            // Restore cache controller and config
            if ($cache_controller_content !== false) {
                file_put_contents($cache_controller_file, $cache_controller_content);
            }
            if ($cache_config_content !== false) {
                file_put_contents($cache_config_file, $cache_config_content);
            }

            // Clear template files if requested
            if ($drop_template_files) {
                $this->clearTemplateFiles();
            }
        }

        // Reload schema cache
        $CI = &get_instance();
        $CI->crmentity->reloadSchemaCache();
        //die('test');
        return true;
    }

    private function removeDirectory($path)
    {
        if (is_dir($path)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }

            rmdir($path);
        } elseif (is_file($path)) {
            unlink($path);
        }
    }

    private function clearTemplateFiles()
    {
        $template_build_path = APPPATH . '../template/build/';
        $files = new DirectoryIterator($template_build_path);

        foreach ($files as $file) {
            if ($file->isFile() && $file->getFilename() != '.gitkeep') {
                unlink($file->getRealPath());
            }
        }
    }

    public function clearEntityCache($entity)
    {
        $tags = $this->buildTagsFromEntity($entity);
        //debug($tags);
        $this->clearCacheTags($tags);
    }

    public function clearCacheKey($key = null)
    {
        if (!$key) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        $this->delete($key);

        $this->clearCache(false, 'raw_queries');
    }

    public function clearCacheTags($tags)
    {
        if (!$tags) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        $this->deleteByTags($tags);

        $this->clearCache(false, 'raw_queries');
    }

    public function clearCacheRecord($entity = null, $id = null)
    {
        if (!$entity || !$id) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }
        $cache_key = "apilib.item.{$entity}.{$id}";

        return $this->clearCacheKey($cache_key);
    }
    public function setCurrentConfig($config)
    {
        $config_json = json_encode($config, JSON_PRETTY_PRINT);
        file_put_contents(APPPATH . 'cache/cache-config.json', $config_json);
    }
    public function getCurrentConfig()
    {
        $cache_config_json = @file_get_contents(APPPATH . 'cache/cache-config.json');
        $cache_config = json_decode($cache_config_json, true);
        return $cache_config;
    }
    public function switchActive($key, $val)
    {
        $config = $this->getCurrentConfig();
        $config[$key]['active'] = $val;
        $this->setCurrentConfig($config);
    }

    public function getModifiedDate()
    {
        $modified_dates = [];
        $modified_dates['database_schema'] = @stat(APPPATH . 'cache/crm.schema')['mtime'];
        $modified_dates['apilib'] = @stat(APPPATH . 'cache/apilib')['mtime'];
        $modified_dates['raw_queries'] = @stat(APPPATH . 'cache/sql')['mtime'];
        $modified_dates['full_page'] = @stat(APPPATH . 'cache/fullpages')['mtime'];
        $modified_dates['template_assets'] = @stat(APPPATH . '../template/build')['mtime'];

        return $modified_dates;
    }

    public function getDiskSpace()
    {
        $diskspaces = [];
        $diskspaces['database_schema'] = @filesize(APPPATH . 'cache/crm.schema');
        $diskspaces['apilib'] = GetDirectorySize(APPPATH . 'cache/apilib');
        $diskspaces['raw_queries'] = GetDirectorySize(APPPATH . 'cache/sql');
        $diskspaces['full_page'] = GetDirectorySize(APPPATH . 'cache/fullpages');
        $diskspaces['template_assets'] = GetDirectorySize(APPPATH . '../template/build');
        return $diskspaces;
    }
    public function isActive($key)
    {
        return !empty($this->getCurrentConfig()[$key]['active']);
    }
    public function buildTagsFromEntity($entity, $value_id = null)
    {
        $cacheKey = $entity . ($value_id ? ":{$value_id}" : "");

        if (isset($this->entityTagsCache[$cacheKey])) {
            return $this->entityTagsCache[$cacheKey];
        }

        $CI = &get_instance();
        $entity_data = $CI->crmentity->getEntity($entity);
        $tags = [$entity_data['entity_name']];

        if ($value_id) {
            $tags[] = "{$entity_data['entity_name']}:{$value_id}";
        }

        // Fetch all fields in one query
        $fields = $CI->crmentity->getFields($entity_data['entity_id']);

        foreach ($fields as $field) {
            if ($field['fields_ref_auto_right_join'] == DB_BOOL_TRUE || $field['fields_ref_auto_left_join'] == DB_BOOL_TRUE) {
                $tags[] = $field['fields_ref'];
            }
        }

        // Fetch all referencing fields in one query
        $fields_referencing = $CI->crmentity->getFieldsRefBy($entity, false);

        foreach ($fields_referencing as $field) {
            $tags[] = $field['entity_name'];
        }

        $tags = array_filter(array_unique($tags), 'strlen');

        $this->entityTagsCache[$cacheKey] = $tags;

        return $tags;
    }
}
