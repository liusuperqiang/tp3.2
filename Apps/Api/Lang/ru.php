<?php
return array(
    //订单列表
    'order_pay_money'=>'Оплатить',
    'order_cancel'=>'Отменить',
    'order_view_wuliu'=>'Статус доставки',
    'order_comment_shaidan'=>'Написать отзыв',
    'order_view_order'=>'Проверить заказ',
    'order_refunddetail'=>'Возврат денег',
    'order_refund'=>'Возврат денег',
    'order_status_cancel'=>'аннулирован',
    'order_status_invalid'=>'недействителен',
    'order_status_refund'=>'возврат товара',

    //消息格式  ACTION_msg_type   错误用  ACTION_error_type
    'global_error_api'=>'Ошибка проверки подписи API',
    'global_error_user'=>'Не удалось получить информацию о пользователе.',



    'getcoupon_error_couponerr'=>'Купон не существует или был удален.',
    'getcoupon_msg_null'=>' Купон был деактивирован, к сожалению Вы не успели, пожалуйста попробуйте в следующий раз.',
    'getcoupon_msg_limitnum'=>'Каждый пользователь может активировать {$limitnum} купонов. Вы уже получили {$usergetcount} купон. К сожалению Ваш лимит исчерпан.',
    'getcoupon_msg_limitold'=>'Данный купон не предназначен для новых пользователей. Сначала Вам необходимо оформить заказ.',
    'getcoupon_msg_limitnew'=>'Данный купон предназначен только для новых пользователей.',
    'getcoupon_msg_limitrank'=>'К сожалению данный купон доступен {$for_rank_name} пользователям. Вы не являетесь участником программы.',
    'getcoupon_msg_success'=>'Поздравляем! Вы успешно получили купон на сумму {$amount} тг., Вы можете перейти в раздел «Купоны», чтобы проверить полученный купон. Используйте купон при оформлении заказа.',

    //订单页面优惠券
    'order_error_goodsnull'=>'Информация о продукте неполная, вернитесь и повторно отправьте заказ',
    'order_error_nostrat'=>'Время начала действия купона:{$starttime}',
    'order_error_stop'=>'Срок действия купона истек',
    'order_error_limitamount'=>'Купон можно использовать только в том случае, если общая сумма подходящих товаров заказа превышает {$limitamount} тг.',
    'order_error_all_limitamount'=>'Купон можно использовать, только если общая сумма заказа превышает {$limitamount} тг.',
    'order_error_nocoupon'=>'Купонов нет',

    'order_msg_has_enable_coupon'=>'На данный заказ можно использовать купон',

    //地址
    'setdefault_address_success'=>'Адрес по умолчанию установлен успешно',
    'address-error-datanull'=>'Текущая информация об адресе доставки не существует или была удалена',
    'address-error-notdel'=>'Адрес доставки по умолчанию не разрешается удалять',
    'address-msg-delsuccess'=>'Адрес доставки успешно удален',
    'address-msg-checkcityhasdistrict'=>'Пожалуйста, выберите район',
    'address-error-checkcityhasdistrict-null'=>'Текущая информация о городе неверна, пожалуйста, повторно выберите информацию о Вашем городе',

    'address-error-mobileerr'=>'Введите номер телефона',
    'address-error-consigneeerr'=>'Укажите пожалуйста имя и фамилию получателя',
    'address-error-firsterr'=>'Укажите пожалуйста имя получателя',
    'address-error-lasterr'=>'Укажите пожалуйста фамилию',
    'address-msg-savesuccess'=>'Сохранено успешно',
    'address-error-addresserr'=>'Введите подробный адрес',
    'address-error-cityerr'=>'Пожалуйста, выберите информацию о Вашем городе',
    'address-error-districterr'=>'Пожалуйста, выберите район',
    'address-error-needmodify'=>'Ожидайте завершения',


    'page-title-reg' => '注册SRCSHOP会员',

//商品页面
    'the_goods_by'   =>'Данный товар находится на',
    'estimated_time_of_arrival'   =>'до',
    'shipments'   =>'Срок доставки до пункта получения заказа от',
    'day'   =>'Дней',
    'commodity_details'=>'Описание товара',
    'buy_immediately'=>'Купить',
    'buy_immediately_coupon'=>'Регистрируйся и получи купон на 2000 тг.',
    'buy_createorder'=>'Создать заказ',
    'sku-stock'=>'В наличии',
    'piece'=>'шт.',
    'selected'=>'Выбрано',
    'sku-name'=>'Артикул',
    'buy_num'=>'Количество',
    'go2buy'=> 'Подтвердить',
    'err-goodsdata'=>'Ошибка в сведениях о товарах, перезагрузите страницу',
    'goods-attr-err'=>'У товара несколько категорий, пожалуйста, выберете нужную Вам',
    'goods-attr-err1'=>'Ошибка в сведениях о категориях товара, перезагрузите страницу',
    'goods-buymax-err'=>'Максимальное количество товара для покупки {$max}',

//订单
    'buy-title'=>'Данные для оформления заказа',
    'order-title-address'=>'Адрес получателя',
    'order-key-consignee'=>'Получатель',
    'order-plc-consignees'=>'Введите имя получателя',
    'order-key-fname'=>'Имя',
    'order-plc-fname'=>'Введите Имя',
    'order-key-lname'=>'Фамилия',
    'order-plc-lname'=>'Введите Фамилия',
    'order-key-mobile'=>'Номер телефона',
    'order-plc-mobile'=>'(•••) •••-••-••',
    'order-key-at'=>'Ваш регион',
    'order-key-address'=>'Подробный адрес',
    'order-key-book'=>'Комментарий',
    'order-plc-book'=>'Оставьте комментарий к заказу',
    'order-key-payment'=>'Способ оплаты',
    'error-goodsdata'=>'Ошибка при чтении данных,закройте и откройте приложение заново',
    'order-max'=>'Превышено максимальное количество товара',
    'order-min'=>'Выберете хотя бы 1 штуку товара',
    'order-key-province'=>'Область',
    'order-key-city'=>'Город',
    'order-key-district'=>'Район',
    'order-err-consignee'=>'Введите достоверные данные получателя',
    'order-key-goods-amount'=>'Итого товаров',
    'order-key-shipping-fee'=>'Курьерская доставка',
    'order-key-order-amount'=>'Итого',
    'order-err-payment'=>'Выберите способ оплаты',
    'order-err-goods'=>'Ошибка в сведениях о товарах, перезагрузите страницу',
    'order-err-mobile'=>'Введите действующий номер телефона',
    'order-err-at'=>'Выберите регион',
    'order-err-address'=>'Заполните подробный адрес',
    'order-err-notbuy'=>'Количество выбранного товара превышено, невозможно совершить покупку',
    'order-err-notbuy-price'=>'невозможно совершить покупку',
    'order-success'=>'Заказ оформлен',

//详情
    'res-detail'=>'Ваш заказ ',
    'res-title-base'=>'基本信息',
    'res-title-consignee'=>'Адрес получателя',
    'res-title-goods'=>'订单商品',
    'res-title-amount'=>'付款信息',
    'res-dataerror'=>'Ошибка при чтении данных,закройте и откройте приложение заново',
    'res-err-order'=>'Данный заказ удален или не существует',
    'res-key-ordersn'=>'Номер заказа',
    'res-key-status'=>'当前状态',
    'res-key-invoiceno'=>'Номер посылки',
    'res-key-track'=>'Статус посылки',
    'res-key-consignee'=>'Получатель',
    'res-key-mobile'=>'Номер телефона',
    'res-key-address'=>'Подробный адрес',
    'res-key-goodsamount'=>'Итого товаров',
    'res-key-shippingfee'=>'Курьерская доставка',
    'res-key-totalamount'=>'К оплате',
    'res-status-unconfirm'=>'Неоплачен',
    'res-status-cancel'=>'Отменен',
    'res-status-unvali'=>'无效订单',
    'res-status-returned'=>'Возвращен',
    'res-status-shipping'=>'Оплачен',
    'res-status-shipped'=>'Отправлен',
    'res-status-over'=>'Завершен',
    'res-topay'=>'Оплата картой',
    'res-key-exc'=>'Курс USD на сегодня',
    'res-key-payusd'=>'К оплате',
    'res-paypending-tonglian'=>'Оплата осуществляется по курсу USD на момент оплаты картой VISA или Master card. Проведите оплату в течение 30 минут, иначе возможно изменение курса USD.<br />При возникновении трудности с онлайн оплатой просим обратиться в колл центр <a href="tel:+8(700)8360661">8(700)8360661.</a><br />Курс взят с сайта национального банка Казахстана <a href="https://nationalbank.kz/">https://nationalbank.kz/</a>',
    'res-paypending-paybox'=>'Ваша заявка на покупку успешно отправлена. Пожалуйста, оплатите заказ в течение 30 минут.',
    'res-paypending-kassa24'=>'Ваша заявка на покупку успешно отправлена. Пожалуйста, оплатите заказ в течение 30 минут.',
    'res-paypending-epay'=>'Ваша заявка на покупку успешно отправлена. Пожалуйста, оплатите заказ в течение 30 минут.',
    'res-paysuccess'=>'Поздравляем, Вы успешно совершили покупку. Планируемая дата доставки {$recvdate}. Номер клиентской службы SRC SHOP {$srvtele} (09:00-18:00). Возврат и обмен товара в течение 14 дней.',
    'res-payfail'=>'Оплата не прошла. Вы можете попробовать снова',
    'res-torepay'=>'Оплатить снова',
    'res-op-rebuy'=>'Купить еще',
    'res-copy'=>'Копировать',
    'res-copy-success'=>'Скопировано',

    //20200622
    'shipping_aging'=>'Срок доставки',
    'select_shipping_area'=>'Пожалуйста, выберите регион доставки',
    'shipping_aging_area'=>'Регион доставки',
    'please_select'=>'Выбрать',
    'shippingdata-err-data'=>'Заполните все данные о доставке!',
    'shippingdata-default-shipping'=>'Время доставки заказа отображается на странице службы доставки.',
    'shippingdata-aging-all'=>'Примерный срок доставки от {$mindays} до {$maxdays} дней, доставка осуществляется курьерской службой {$logistics}.',
    'shippingdata-aging'=>'Примерный срок доставки от {$mindays} до {$maxdays} дней, доставка осуществляется курьерской службой партнером SRCSHOP.',

    //  2020 0729
    'order-err-notonsale'=>'Данный товар снят с продажи, поэтому к заказу не подлежит.',

    //20200827
    'order-key-shippingmode'=>'Способ доставки',
    'order-key-pickpoint'=>'Выбрать пункта выдачи',

    'order-key-shippingmode-title'=>'Способ доставки',
    'order-key-pickupmode-title'=>'Выбрать пункта выдачи',

    'order-err-piuckup'=>'Выбрать пункта выдачи',
    'order-err-shipping-notsupport'=>'Этот способ доставки не поддерживается в текущем городе.',
    'order-err-shipping'=>'Выберите способ доставки',
    'order-err-shipping-nothas'=>'Пожалуйста, выберите правильный способ доставки',

    //20201212
    'tip_usecoupon'=>'На данный товар действителен купон на сумму 2 000 тг.',
    'tobuy_usecoupon'=>'Получить купон',
    'tobuy_nocoupon'=>'Без купона',


    //20201223
    'sk-from'=>'До',
    'sk-begin'=>'Начало',
    'sk-finish'=>'конца',
    'sk-left'=>'АКЦИИ осталось',

    //注册模块
    'reg-title-getapp'=>'Скачать приложение',
    'reg-title-page'=>'Регистрация',
    'reg-title-pagetitle'=>'Регистрация SRCSHOP',
    'reg-err-mobilenregx' => 'Пожалуйста, введите корректно номер телефона',
    'reg-err-pwdlen' => 'Пожалуйста, введите пароль для входа не более 16 цифр',
    'reg-err-vcodelen' => 'Пожалуйста, введите 4-значный код подтверждения',
    'reg-err-regetcode' => 'Пожалуйста, получите код подтверждения еще раз',
    'reg-err-codetimeout' => 'Срок действия кода подтверждения истек, пожалуйста получите еще раз',
    'reg-err-codeerror' => 'Ошибка ввода кода подтверждения, пожалуйста проверьте еще раз',
    'reg-err-parent' => 'Ошибка с указанной ссылкой',
    'reg-err-hasmobile' => 'Номер уже зарегистрирован, пожалуйста, измените его или свяжитесь со службой поддержки для подтверждения',
    'reg-key-getcode' => 'Получить код',
    'reg-key-sending' => 'В процессе',
    'reg-err-getcodefail' => 'Не удалось получить код подтверждения, попробуйте еще раз',
    'reg-action-continue'=>'Далее',
    'reg-action-doreg'=>'Регистрация',
    'reg-title-goodslist'=>'Вас может это заинтересовать',
    'reg-title-adva'=>'<span>SRC SHOP</span> интернет магазин нового поколения',

    //领券模块
    'coupon_getlink' => 'Получить купон',
    'coupon_error_all' => 'Ссылка текущего купона неверна, пожалуйста, получите купон по правильной ссылке',
    'coupon_error_notatlink' => 'Текущий купон нельзя получить с помощью этого метода, пожалуйста, заберите купон по правильной ссылке',
    'coupon_error_atlimitapp' => 'Пожалуйста, введите купонный центр приложения, чтобы получить купон',
    'coupon_title_couponbox'=>'Максимум можете получить',
    'coupon_tip_confirm'=>'Подсказка',
    'coupon_title_fllowbox'=>'Инструкция по применению',
    'coupon_title_rulebox'=>'Правила',
    'coupon_key_slogan'=>'Отправка товара производится в день оформления заказа.',
    'coupon_key_address'=>'Город Караганда, улица Мельничная 24А, офис 6.',
    'coupon_limitday'=>'Срок действия {$limitdays} дней после получения',
    'coupon_limitdate'=>'Срок действия купона до {$limitdate}  г. включительно',
    'coupon_btn_get'=>'Получить купон',
    'coupon-err-mobilenregx' => 'Пожалуйста, введите корректно номер телефона',

    'coupon-err-mobileerr'=>'Пожалуйста, введите корректно номер телефона',
    'coupon-err-notreg'=>'Вы не зарегистрированы в  приложении SRC SHOP.',
    'coupon-action-reg'=>'Регистрация',
    'coupon-err-parerr'=>'Купон не существует или был удален.',
    'coupon-err-couponnull'=>'Купон не существует или был удален.',
    'coupon-msg-usenull'=>'Купон был деактивирован, к сожалению Вы не успели, пожалуйста попробуйте в следующий раз.',
    'coupon-msg-limitnum'=>'Каждый пользователь может получить только {$limitnum} купон, Ваш лимит исчерпан.',
    // Каждый пользователь может активировать {$limitnum} купонов. Вы уже получили {$usergetcount} купон. К сожалению Ваш лимит исчерпан.
    'coupon-msg-limitold'=>'Данный купон не предназначен для новых пользователей. Сначала Вам необходимо оформить заказ.',
    'coupon-msg-limitnew'=>'Данный купон предназначен только для новых пользователей.',
    'coupon-msg-limitrank'=>'К сожалению данный купон доступен {$for_rank_name} пользователям. Вы не являетесь участником программы.',
    'coupon-msg-success'=>'Поздравляем! Вы успешно получили купон на сумму {$amount} тг. Вы можете перейти в раздел «Профиль», чтобы проверить полученный купон. Используйте купон при оформлении заказа.',


    // 非法操作
    'user-Illegal-operation' => 'Незаконная операция',
    'set-operation-success' => 'Успешно установлен',
    'set-operation-fail' => 'Ошибка установки',

    'delete-success' =>'успешно удален',

    //收藏
    'collect-goods-success' => 'Успех коллекции',
    'collect-goods-fail' => 'Ошибка сервера',

    'coupon-title-getsuccess'=>'Купон успешно получен',
    'coupon-title-getfail'=>'Купон недействителен',
    'coupon-action-reget'=>'Повторно получить купон',
    'coupon-action-order'=>'Перейти в приложение',

    'promtebox-title'=>'Супер акция',
    'goods-sold'=>'Продано',
    'goods-moneyunit'=>'тг.',

    
);
