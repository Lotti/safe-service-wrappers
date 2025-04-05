
extern zend_class_entry *safeservicewrapper_myredis_ce;

ZEPHIR_INIT_CLASS(SafeServiceWrapper_MyRedis);

PHP_METHOD(SafeServiceWrapper_MyRedis, auth);
PHP_METHOD(SafeServiceWrapper_MyRedis, getAuth);

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_safeservicewrapper_myredis_auth, 0, 1, _IS_BOOL, 0)
	ZEND_ARG_INFO(0, credentials)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_safeservicewrapper_myredis_getauth, 0, 0, IS_MIXED, 0)
ZEND_END_ARG_INFO()

ZEPHIR_INIT_FUNCS(safeservicewrapper_myredis_method_entry) {
	PHP_ME(SafeServiceWrapper_MyRedis, auth, arginfo_safeservicewrapper_myredis_auth, ZEND_ACC_PUBLIC)
	PHP_ME(SafeServiceWrapper_MyRedis, getAuth, arginfo_safeservicewrapper_myredis_getauth, ZEND_ACC_PUBLIC)
	PHP_FE_END
};
