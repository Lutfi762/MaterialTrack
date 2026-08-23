-- Reset data item, kategori, dan transaksi terkait. Fresh start.
SET FOREIGN_KEY_CHECKS = 0;

-- Transactions: inventory stock
TRUNCATE TABLE stock_movements;
TRUNCATE TABLE stock_ins;
TRUNCATE TABLE stock_outs;
TRUNCATE TABLE stock_transfers;
TRUNCATE TABLE stock_adjustments;
TRUNCATE TABLE stock_opnames;
TRUNCATE TABLE stock_returns;
TRUNCATE TABLE stock_reservations;
TRUNCATE TABLE bulk_stock_operations;
TRUNCATE TABLE bulk_stock_operation_items;
TRUNCATE TABLE stock_alert_configs;

-- Transactions: purchasing / sales (detail rows reference items)
TRUNCATE TABLE delivery_items;
TRUNCATE TABLE goods_receipt_items;
TRUNCATE TABLE purchase_invoice_items;
TRUNCATE TABLE purchase_order_items;
TRUNCATE TABLE purchase_request_items;
TRUNCATE TABLE quotation_items;
TRUNCATE TABLE sales_invoice_items;
TRUNCATE TABLE sales_order_items;
TRUNCATE TABLE sales_return_items;
TRUNCATE TABLE sales_returns;
TRUNCATE TABLE sales_invoices;
TRUNCATE TABLE sales_orders;
TRUNCATE TABLE deliveries;
TRUNCATE TABLE quotations;
TRUNCATE TABLE purchase_invoices;
TRUNCATE TABLE goods_receipts;
TRUNCATE TABLE purchase_orders;
TRUNCATE TABLE purchase_requests;

-- Master: items, categories
TRUNCATE TABLE items;
TRUNCATE TABLE category_department;
TRUNCATE TABLE categories;

-- Audit trail dari data lama
TRUNCATE TABLE activity_logs;

SET FOREIGN_KEY_CHECKS = 1;