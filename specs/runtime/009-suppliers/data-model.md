# Suppliers — Data Model

## supplier_profiles

| Column                  | Type         | Notes                            |
| ----------------------- | ------------ | -------------------------------- |
| id                      | bigint PK    |                                  |
| user_id                 | bigint FK UQ | → users.id, cascade delete       |
| company_name_ar         | varchar      | required display name            |
| company_name_en         | varchar null |                                  |
| commercial_reg          | varchar null |                                  |
| tax_number              | varchar null |                                  |
| city                    | varchar null | indexed filter                   |
| district                | varchar null |                                  |
| address                 | varchar null |                                  |
| phone                   | varchar null |                                  |
| verification_status     | string       | pending \| verified \| suspended |
| verified_at             | timestamp n  | set when verified                |
| rating_avg              | decimal(3,2) | default 0                        |
| total_ratings           | unsigned int | default 0                        |
| created_at / updated_at | timestamps   |                                  |

## products (additive)

| Column      | Type        | Notes                                         |
| ----------- | ----------- | --------------------------------------------- |
| supplier_id | bigint FK n | → supplier_profiles.id, indexed, nullOnDelete |
