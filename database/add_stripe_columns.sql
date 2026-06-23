-- Add Stripe-related columns to users table
ALTER TABLE `users` ADD COLUMN `stripe_customer_id` VARCHAR(255) NULL AFTER `api_quota_used`;
ALTER TABLE `users` ADD COLUMN `stripe_subscription_id` VARCHAR(255) NULL AFTER `stripe_customer_id`;

-- Add index for faster lookups
CREATE INDEX `idx_stripe_customer_id` ON `users`(`stripe_customer_id`);
CREATE INDEX `idx_stripe_subscription_id` ON `users`(`stripe_subscription_id`);
