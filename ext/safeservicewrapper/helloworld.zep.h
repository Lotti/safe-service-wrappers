
extern zend_class_entry *safeservicewrapper_helloworld_ce;

ZEPHIR_INIT_CLASS(SafeServiceWrapper_HelloWorld);

PHP_METHOD(SafeServiceWrapper_HelloWorld, say);

ZEND_BEGIN_ARG_INFO_EX(arginfo_safeservicewrapper_helloworld_say, 0, 0, 0)
ZEND_END_ARG_INFO()

ZEPHIR_INIT_FUNCS(safeservicewrapper_helloworld_method_entry) {
PHP_ME(SafeServiceWrapper_HelloWorld, say, arginfo_safeservicewrapper_helloworld_say, ZEND_ACC_PUBLIC|ZEND_ACC_STATIC)
	PHP_FE_END
};
