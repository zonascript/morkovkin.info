CREATE TABLE `accounts`
(
    `id`                    integer AUTO_INCREMENT KEY,
    `enabled`               boolean DEFAULT TRUE,                           -- Account enabled state
    `name`                  varchar(64),                                    -- Account name
    `email`                 varchar(64),                                    -- Account email
    `messenger`             varchar(64),                                    -- Account messenger
    `username`              varchar(32),                                    -- Username
    `password`              varchar(32),                                    -- Password MD5
    `created`               integer
);

CREATE TABLE `products`
(
    `id`                    integer AUTO_INCREMENT KEY,
    `enabled`               boolean DEFAULT TRUE,                           -- Product enabled state
    `name`                  varchar(64) UNIQUE KEY,                         -- Product name
    `type`                  set('CUSTOM'),                                  -- Product type
    `base_price`            decimal(10, 2) UNSIGNED,                        -- Product price
    `page_price`            decimal(10, 2) UNSIGNED,                        -- Product page price
    `extra_price`           decimal(10, 2) UNSIGNED,                        -- Product extra price
    `created`               integer                                         -- Product timestamp
);

CREATE TABLE `orders`
(
    `id`                    integer AUTO_INCREMENT KEY,
    `account_id`            integer,
    `product_id`            integer,
    `project_name`          varchar(128),
    `project_task`          text,
    `type`                  enum('DOWNLOAD', 'REDESIGN', 'CUSTOM'),         -- Product type
    `status`                enum('WAIT', 'PAID', 'WORK', 'DONE'),           -- Order status
    `price`                 decimal(10,2) UNSIGNED,                         -- Product price
    `created`               integer                                         -- Product timestamp
);

CREATE TABLE `history`
(
    `id`                    integer AUTO_INCREMENT KEY,                     -- Event ID
    `account_id`            integer,                                        -- Associated account ID
    `product_id`            integer,                                        -- Associated product ID
    `order_id`              integer,                                        -- Associated order ID
    `type`                  varchar(16),                                    -- Event type (includes payments)
    `param_1`               varchar(128),                                   -- Event parameter #1
    `param_2`               varchar(32),                                    -- Event parameter #2
    `param_3`               varchar(32),                                    -- Event parameter #3
    `created`               integer                                         -- Event timestamp
);

CREATE TABLE `groups`
(
    `id`                    integer AUTO_INCREMENT KEY,                     -- Group ID, string identifier
    `enabled`               boolean DEFAULT TRUE,                           -- Group enabled state
    `type`                  enum('PREMIUM', 'STANDARD', 'NEW'),             -- Group type
    `name`                  varchar(32)                                     -- Group name
);

CREATE TABLE `options`
(
    `id`                    integer AUTO_INCREMENT KEY,                     -- Option ID, string identifier
    `enabled`               boolean DEFAULT TRUE,                           -- Option enabled state
    `name`                  varchar(32),                                    -- Option name
    `price`                 decimal(10,2) UNSIGNED                          -- Option price
);

CREATE TABLE `discounts`
(
    `id`                    integer AUTO_INCREMENT KEY,                     -- Discount ID
    `enabled`               boolean DEFAULT TRUE,                           -- Discount enabled state
    `account_id`            integer,                                        -- Account ID
    `product_id`            integer,                                        -- Product ID
    `value`                 varchar(8)                                      -- May be price '15.00' or percentage '5.00%'
);

CREATE TABLE `product_groups`
(
    `product_id`            integer,                                        -- Product ID
    `group_id`              integer,                                        -- Group ID
    
    PRIMARY KEY (`product_id`, `group_id`)
);

CREATE TABLE `product_options`
(
    `product_id`            integer,                                        -- Product ID
    `option_id`             integer,                                        -- Option ID
    
    PRIMARY KEY (`product_id`, `option_id`)
);

CREATE TABLE `order_discounts`
(
    `order_id`              integer,                                        -- Order ID
    `discount_id`           integer,                                        -- Discount ID
    
    PRIMARY KEY (`order_id`, `discount_id`)
);

CREATE TABLE `order_options`
(
    `order_id`              integer,                                        -- Order ID
    `option_id`             integer,                                        -- Option ID
    
    PRIMARY KEY (`order_id`, `option_id`)
);

INSERT INTO `accounts` SET `id` = 1, `email` = 'support@skinstore.net', `created` = UNIX_TIMESTAMP();

INSERT INTO `products` SET `id` = 1, `base_price` = 340.00, page_price = 40.00, extra_price = null, `type` = 'CUSTOM', `created` = UNIX_TIMESTAMP();
INSERT INTO `products` SET `id` = 2, `base_price` = 1200.00, page_price = 25.00, extra_price = 60.00, `type` = NULL, `created` = UNIX_TIMESTAMP();
INSERT INTO `products` SET `id` = 3, `base_price` = 600.00, page_price = 25.00, extra_price = 60.00, `type` = NULL, `created` = UNIX_TIMESTAMP();

INSERT INTO `groups` SET `id` = 1, `type` = 'PREMIUM', `name` = 'Premium';
INSERT INTO `groups` SET `id` = 2, `type` = 'STANDARD', `name` = 'Standard';
INSERT INTO `groups` SET `id` = 3, `type` = 'STANDARD', `name` = 'Best Price';
INSERT INTO `groups` SET `id` = 4, `type` = 'NEW', `name` = 'New';

INSERT INTO `options` SET `id` = 1, `price` = 50, `name` = 'Gold coders script';
INSERT INTO `options` SET `id` = 2, `price` = 100, `name` = '5 inner pages';
INSERT INTO `options` SET `id` = 3, `price` = 60, `name` = '4 animated banners';
INSERT INTO `options` SET `id` = 4, `price` = 180, `name` = 'Flash slider';
INSERT INTO `options` SET `id` = 5, `price` = 80, `name` = 'Unique content';

INSERT INTO `product_options` (`product_id`, `option_id`) VALUES (1, 1);
INSERT INTO `product_options` (`product_id`, `option_id`) VALUES (1, 2);
INSERT INTO `product_options` (`product_id`, `option_id`) VALUES (1, 3);
INSERT INTO `product_options` (`product_id`, `option_id`) VALUES (1, 4);
INSERT INTO `product_options` (`product_id`, `option_id`) VALUES (1, 5);
INSERT INTO `product_options` (`product_id`, `option_id`) VALUES (2, 1);
INSERT INTO `product_options` (`product_id`, `option_id`) VALUES (2, 2);
INSERT INTO `product_options` (`product_id`, `option_id`) VALUES (2, 3);
INSERT INTO `product_options` (`product_id`, `option_id`) VALUES (2, 4);
INSERT INTO `product_options` (`product_id`, `option_id`) VALUES (2, 5);
INSERT INTO `product_options` (`product_id`, `option_id`) VALUES (3, 1);
INSERT INTO `product_options` (`product_id`, `option_id`) VALUES (3, 2);
INSERT INTO `product_options` (`product_id`, `option_id`) VALUES (3, 3);

INSERT INTO `product_groups` (`product_id`, `group_id`) VALUES (2, 1);
INSERT INTO `product_groups` (`product_id`, `group_id`) VALUES (3, 2);
INSERT INTO `product_groups` (`product_id`, `group_id`) VALUES (3, 4);

INSERT INTO `discounts` (`account_id`, `product_id`, `value`) VALUES (null, null, '5%');
INSERT INTO `discounts` (`account_id`, `product_id`, `value`) VALUES (1, null, '10%');
INSERT INTO `discounts` (`account_id`, `product_id`, `value`) VALUES (null, 2, '10.00');