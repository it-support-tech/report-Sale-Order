-- NTP Trading Petroleum - Sales Order system schema (PostgreSQL)

CREATE TABLE customers (
    id              SERIAL PRIMARY KEY,
    code            VARCHAR(20) NOT NULL UNIQUE,
    company_name    VARCHAR(255) NOT NULL,
    village         VARCHAR(255),
    district        VARCHAR(255),
    province        VARCHAR(255),
    contact_person  VARCHAR(255),
    phone           VARCHAR(100),
    fax             VARCHAR(100),
    tax_id          VARCHAR(100),
    payment_term    VARCHAR(100),
    ship_to_address TEXT,
    created_at      TIMESTAMP NOT NULL DEFAULT now(),
    updated_at      TIMESTAMP NOT NULL DEFAULT now()
);

CREATE TABLE company_banks (
    id           SERIAL PRIMARY KEY,
    bank_name    VARCHAR(255) NOT NULL,
    account_lak  VARCHAR(100),
    account_thb  VARCHAR(100),
    account_usd  VARCHAR(100),
    swift_code   VARCHAR(50),
    is_active    BOOLEAN NOT NULL DEFAULT true,
    created_at   TIMESTAMP NOT NULL DEFAULT now(),
    updated_at   TIMESTAMP NOT NULL DEFAULT now()
);

CREATE TABLE products (
    id                  SERIAL PRIMARY KEY,
    code                VARCHAR(50) NOT NULL UNIQUE,
    name                VARCHAR(255) NOT NULL,
    uom                 VARCHAR(20) NOT NULL DEFAULT 'Liter',
    default_unit_price  NUMERIC(18,2) NOT NULL DEFAULT 0,
    created_at          TIMESTAMP NOT NULL DEFAULT now(),
    updated_at          TIMESTAMP NOT NULL DEFAULT now()
);

CREATE TABLE warehouses (
    id    SERIAL PRIMARY KEY,
    code  VARCHAR(20) NOT NULL UNIQUE,
    name  VARCHAR(255)
);

CREATE TABLE sales_orders (
    id              SERIAL PRIMARY KEY,
    document_no     VARCHAR(50) NOT NULL UNIQUE,
    document_date   DATE NOT NULL,
    delivery_date   DATE,
    reference_no    VARCHAR(100),
    currency        VARCHAR(3) NOT NULL DEFAULT 'LAK',
    warehouse_code  VARCHAR(20) NOT NULL,
    customer_id     INTEGER NOT NULL REFERENCES customers(id),
    ship_to_address TEXT,
    bank_id         INTEGER REFERENCES company_banks(id),
    payment_term    VARCHAR(100),
    remark          TEXT,
    discount_type   VARCHAR(10) NOT NULL DEFAULT 'amount', -- 'percent' | 'amount'
    discount_value  NUMERIC(18,4) NOT NULL DEFAULT 0,
    vat_percent     NUMERIC(6,2) NOT NULL DEFAULT 10,
    vat_mode        VARCHAR(10) NOT NULL DEFAULT 'exclusive', -- 'exclusive' (add VAT on top) | 'inclusive' (extract VAT from total)
    sub_total       NUMERIC(18,2) NOT NULL DEFAULT 0,
    discount_amount NUMERIC(18,2) NOT NULL DEFAULT 0,
    total           NUMERIC(18,2) NOT NULL DEFAULT 0,
    vat_amount      NUMERIC(18,2) NOT NULL DEFAULT 0,
    net_total       NUMERIC(18,2) NOT NULL DEFAULT 0,
    prepared_by     VARCHAR(255),
    verified_by     VARCHAR(255),
    approved_by     VARCHAR(255),
    created_at      TIMESTAMP NOT NULL DEFAULT now(),
    updated_at      TIMESTAMP NOT NULL DEFAULT now()
);

CREATE TABLE sales_order_items (
    id              SERIAL PRIMARY KEY,
    sales_order_id  INTEGER NOT NULL REFERENCES sales_orders(id) ON DELETE CASCADE,
    line_no         INTEGER NOT NULL,
    product_id      INTEGER REFERENCES products(id),
    product_code    VARCHAR(50),
    product_name    VARCHAR(255),
    quantity        NUMERIC(18,3) NOT NULL DEFAULT 0,
    uom             VARCHAR(20) NOT NULL DEFAULT 'Liter',
    unit_price      NUMERIC(18,2) NOT NULL DEFAULT 0,
    disc_amount     NUMERIC(18,2) NOT NULL DEFAULT 0,
    amount          NUMERIC(18,2) NOT NULL DEFAULT 0
);

CREATE TABLE sales_order_deliveries (
    id              SERIAL PRIMARY KEY,
    sales_order_id  INTEGER NOT NULL REFERENCES sales_orders(id) ON DELETE CASCADE,
    row_order       INTEGER NOT NULL,
    delivery_date   DATE,
    sales_order_no  VARCHAR(50),
    liters          NUMERIC(18,3) NOT NULL DEFAULT 0,
    delivery_note_no VARCHAR(50),
    ar_invoice_no   VARCHAR(50),
    tax_no          VARCHAR(50)
);

CREATE INDEX idx_sales_order_items_order ON sales_order_items(sales_order_id);
CREATE INDEX idx_sales_order_deliveries_order ON sales_order_deliveries(sales_order_id);
CREATE INDEX idx_sales_orders_customer ON sales_orders(customer_id);
