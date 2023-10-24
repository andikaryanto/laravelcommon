<?php

namespace LaravelCommon\App\Consts;

class ResponseConst extends BaseConts
{
    //Success
    const OK = [
        'status' => 'OK',
        'code'   => 1000,
    ];

    //Fail
    const INVALID_LOGIN         = [
        'status' => 'INVALID_LOGIN',
        'code'   => 2000,
    ];
    const NO_ACCESS_USER_MODULE = [
        'status' => 'NO_ACCESS_USER_MODULE',
        'code'   => 2001,
    ];
    const FAILED_SAVE_DATA      = [
        'status' => 'FAILED_SAVE_DATA',
        'code'   => 2002,
    ];
    const DATA_NOT_FOUND        = [
        'status' => 'DATA_NOT_FOUND',
        'code'   => 2003,
    ];
    const FAILED_TO_VERIFY      = [
        'status' => 'FAILED_TO_VERIFY',
        'code'   => 2004,
    ];
    const FAILED_TRACK_LOCATION = [
        'status' => 'FAILED_TRACK_LOCATION',
        'code'   => 2005,
    ];
    const NO_DATA_FOUND         = [
        'status' => 'NO_DATA_FOUND',
        'code'   => 2006,
    ];
    const DATA_EXIST            = [
        'status' => 'DATA_EXIST',
        'code'   => 2007,
    ];
    const INVALID_DATA          = [
        'status' => 'INVALID_DATA',
        'code'   => 2008,
    ];
    const FAILED_TO_REGISTER    = [
        'status' => 'FAILED_TO_REGISTER',
        'code'   => 2009,
    ];
    const SESSION_EXPPIRED      = [
        'status' => 'SESSION_EXPPIRED',
        'code'   => 2010,
    ];
    const INVALID_CREDENTIAL    = [
        'status' => 'INVALID_CREDENTIAL',
        'code'   => 2011,
    ];
    const FORBIDDEN             = [
        'status' => 'FORBIDDEN',
        'code'   => 2012,
    ];
    const FAILED_DELETE_DATA    = [
        'status' => 'FAILED_DELETE_DATA',
        'code'   => 2013,
    ];
    const TOKEN_NOT_FOUND       = [
        'status' => 'TOKEN_NOT_FOUND',
        'code'   => 2014,
    ];
    const SESSION_EXPIRED       = [
        'status' => 'SESSION_EXPIRED',
        'code'   => 2015,
    ];
    const PAGE_NOT_FOUND        = [
        'status' => 'PAGE_NOT_FOUND',
        'code'   => 2016,
    ];
    const NOT_AUTHORIZED = [
        'status' => 'NOT_AUTHORIZED',
        'code'   => 2017,
    ];
    const NOT_ALLOWED_METHOD = [
        'status' => 'NOT_ALLOWED_METHOD',
        'code'   => 2018,
    ];
}
