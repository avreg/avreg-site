<?php

namespace Avreg;

/**
 * Derivative classes should perform PTZ operations through different protocols (onvif, axis, etc).
 */
interface PtzInterface
{
    /**
     * Should receive params in $data:
     *
     * cameraNumber - Number
     *
     *
     * Should return JSON:
     *
     * {
     *      coordSpaces: {
     *          pan: {
     *              min: Number,
     *              max: Number
     *          },
     *          tilt: {
     *              min: Number,
     *              max: Number
     *          },
     *          zoom: {
     *              min: Number,
     *              max: Number
     *          }
     *      },
     *      speedSpaces: {          // if supported
     *          pan: {
     *              min: Number,
     *              max: Number
     *          },
     *          tilt: {
     *              min: Number,
     *              max: Number
     *          },
     *          zoom: {
     *              min: Number,
     *              max: Number
     *          }
     *      }
     * }
     *
     * @param array $data
     * @return mixed
     */
    public function getPtzSpaces($data = array());

    /**
     * Should receive params in $data:
     *
     * cameraNumber - Number
     *
     *
     * Should return JSON:
     *
     * {
     *      position: {
     *          pan: Number,
     *          tilt: Number,
     *          zoom: Number
     *      }
     * }
     *
     * @param array $data
     * @return mixed
     */
    public function getPtzStatus($data = array());

    /**
     * Should receive params in $data:
     *
     * cameraNumber - Number
     *
     *
     * Should return JSON:
     *
     * {
     *      presets: [
     *          {
     *              name: String,
     *              token: String,
     *              position: {
     *                  pan: Number,
     *                  tilt: Number,
     *                  zoom: Number
     *              }
     *          },
     *          ...
     *      ]
     * }
     *
     * @param array $data
     * @return mixed
     */
    public function getPtzPresets($data = array());

    /**
     * Should receive params in $data:
     *
     * cameraNumber - Number
     * zoom - Number
     * pan - Number
     * tilt - Number
     *
     *
     * Should return JSON:
     *
     * {
     * }
     *
     * @param array $data
     * @return mixed
     */
    public function moveAbsolute($data = array());

    /**
     * Should receive params in $data:
     *
     * cameraNumber - Number
     *
     * Should return JSON:
     *
     * {
     * }
     *
     * @param array $data
     * @return mixed
     */
    public function moveStop($data = array());

    /**
     * Should receive params in $data:
     *
     * cameraNumber - Number
     * presetToken - String
     *
     * Should return JSON:
     *
     * {
     * }
     *
     * @param array $data
     * @return mixed
     */
    public function gotoPreset($data = array());

    /**
     * Should receive params in $data:
     *
     * cameraNumber - Number
     *
     * Should return JSON:
     *
     * {
     * }
     *
     * @param array $data
     * @return mixed
     */
    public function gotoHomePosition($data = array());

    /**
     * Should receive params in $data:
     *
     * cameraNumber - Number
     *
     * Should return JSON:
     *
     * {
     * }
     *
     * @param array $data
     * @return mixed
     */
    public function setHomePosition($data = array());

    /**
     * Should receive params in $data:
     *
     * cameraNumber - Number
     * presetName - String
     *
     * Should return JSON:
     *
     * {
     * }
     *
     * @param array $data
     * @return mixed
     */
    public function createPreset($data = array());

    /**
     * Should receive params in $data:
     *
     * cameraNumber - Number
     * presetToken - String
     *
     * Should return JSON:
     *
     * {
     * }
     *
     * @param array $data
     * @return mixed
     */
    public function removePreset($data = array());
}
