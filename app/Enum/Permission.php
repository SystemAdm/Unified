<?php

namespace App\Enum;

enum Permission: string
{
    // User permissions
    case SHOW_USER = 'show_user';
    case UPDATE_USER = 'update_user';
    case DELETE_USER = 'delete_user';
    case ADMIN_USER = 'admin_user';
    case CREATE_USER = 'create_user';
    case INDEX_USER = 'index_user';

    // Organization permissions
    case SHOW_ORGANIZATION = 'show_organization';
    case UPDATE_ORGANIZATION = 'update_organization';
    case DELETE_ORGANIZATION = 'delete_organization';
    case ADMIN_ORGANIZATION = 'admin_organization';
    case CREATE_ORGANIZATION = 'create_organization';
    case INDEX_ORGANIZATION = 'index_organization';

    // Email permissions
    case SHOW_EMAIL = 'show_email';
    case UPDATE_EMAIL = 'update_email';
    case DELETE_EMAIL = 'delete_email';
    case ADMIN_EMAIL = 'admin_email';
    case CREATE_EMAIL = 'create_email';
    case INDEX_EMAIL = 'index_email';

    // Location permissions
    case SHOW_LOCATION = 'show_location';
    case UPDATE_LOCATION = 'update_location';
    case DELETE_LOCATION = 'delete_location';
    case ADMIN_LOCATION = 'admin_location';
    case CREATE_LOCATION = 'create_location';
    case INDEX_LOCATION = 'index_location';

    // Permission permissions
    case SHOW_PERMISSION = 'show_permission';
    case UPDATE_PERMISSION = 'update_permission';
    case DELETE_PERMISSION = 'delete_permission';
    case ADMIN_PERMISSION = 'admin_permission';
    case CREATE_PERMISSION = 'create_permission';
    case INDEX_PERMISSION = 'index_permission';

    // Role permissions
    case SHOW_ROLE = 'show_role';
    case UPDATE_ROLE = 'update_role';
    case DELETE_ROLE = 'delete_role';
    case ADMIN_ROLE = 'admin_role';
    case CREATE_ROLE = 'create_role';
    case INDEX_ROLE = 'index_role';

    // Phone permissions
    case SHOW_PHONE = 'show_phone';
    case UPDATE_PHONE = 'update_phone';
    case DELETE_PHONE = 'delete_phone';
    case ADMIN_PHONE = 'admin_phone';
    case CREATE_PHONE = 'create_phone';
    case INDEX_PHONE = 'index_phone';

    // Event permissions
    case SHOW_EVENT = 'show_event';
    case UPDATE_EVENT = 'update_event';
    case DELETE_EVENT = 'delete_event';
    case ADMIN_EVENT = 'admin_event';
    case CREATE_EVENT = 'create_event';
    case INDEX_EVENT = 'index_event';
    case JOIN_EVENT = 'join_event';

    /**
     * Create a permission by combining an access type and a model type.
     *
     * @param Access $access The access type (e.g., SHOW, UPDATE, DELETE)
     * @param Models $model The model type (e.g., USER, ORGANIZATION, EVENT)
     * @return Permission The combined permission
     * @throws \ValueError If the combined permission doesn't exist
     */
    public static function fromAccessAndModel(Access $access, Models $model): self
    {
        $permissionConstant = strtoupper($access->name) . '_' . strtoupper($model->name);

        foreach (self::cases() as $case) {
            if ($case->name === $permissionConstant) {
                return $case;
            }
        }

        throw new \ValueError("Permission {$permissionConstant} does not exist");
    }

    /**
     * Create a permission string by combining an access type and a model type.
     * This method doesn't require the permission to exist in the enum.
     *
     * @param Access $access The access type (e.g., SHOW, UPDATE, DELETE)
     * @param Models $model The model type (e.g., USER, ORGANIZATION, EVENT)
     * @return string The combined permission string
     */
    public static function createFromAccessAndModel(Access $access, Models $model): string
    {
        return $access->value . '_' . $model->value;
    }
}
