<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of perspectives
 *
 * @author Alex
 */

abstract class DisplayPolicy {
    const UDID_GEN_ONTIME = 1;    // UDID specific, else generic, else on-time
    const UDID_GEN_NONE = 2;      // UDID specific, else generic, else no display
}
