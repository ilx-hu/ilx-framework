<?php


namespace Ilx\Module\Security\Model;


use PandaBase\Connection\ConnectionManager;
use PandaBase\Exception\AccessDeniedException;
use PandaBase\Exception\ConnectionNotExistsException;
use PandaBase\Record\SimpleRecord;

/**
 * Class UserRoleAssoc
 *
 * Felhasználó és szerepek összerendelésére szolgáló osztály.
 *
 * @package CityPortal\Model\Auth
 */
class UserRole extends SimpleRecord
{
    /**
     * Hozzáadja a user_id-t a paraméterben kapott role-hoz.
     *
     * @param int $user_id
     * @param int $role_id
     * @return UserRole
     * @throws AccessDeniedException
     * @throws \Exception
     */
    public static function addUserTo($user_id, $role_id) {
        $user_role = new UserRole([
            "user_id" => $user_id,
            "role_id" => $role_id
        ]);
        ConnectionManager::persist($user_role);
        return $user_role;
    }

    /**
     * Visszatér a paraméterben kapott user_id-hoz tartozó role_id-kal.
     *
     * Ha nincs a user_id-hoz tartozó role_id, üres tömbbel tér vissza.
     *
     * @param int $user_id
     * @param bool $include_descendants
     * @return array
     * @throws ConnectionNotExistsException
     */
    public static function getRoles($user_id, $include_descendants = True) {

        /*
         * 1, Lehúzzuk az alap role-okat
         */
        $user_roles = ConnectionManager::getInstanceRecords(UserRole::class, "
            SELECT * FROM cp_user_roles
            WHERE user_id = :user_id
        ", [
            "user_id" => $user_id
        ]);

        /*
         * 1.1, Ha üres volt a tömb nincs több dolgunk
         */
        if(empty($user_roles)) {
            return [];
        }

        /*
         * 1.2, Ha nem kell a leszármazott role-kat lehúzni akkor is végeztünk
         */
        if(!$include_descendants) {
            return [$user_roles['role_id']];
        }

        /*
         * 2. Minden egyes role-hoz lehúzzuk a leszármazottakat
         */
        $roles = [];
        foreach ($user_roles as $user_role) {
            $role = Role::getTreeObject($user_role["role_id"]);
            $descendants = $role->descendants();
            $roles[] = $role["role_id"];
            $roles = array_merge($roles, array_map(function($row){
                return $row["role_id"];
            }, $descendants));
        }
        return array_unique($roles);
    }

    /**
     * Visszadja a paraméterben kapott user_id, role_id-hoz ratozó objektumot. Ha nem létezik, akkor a visszatérési
     * értékben szereplő objektum érvénytelen lesz ($obj->isValid() --> false).
     *
     * @param int $user_id
     * @param int $role_id
     * @return UserRole
     * @throws AccessDeniedException
     */
    public static function getObject($user_id, $role_id) {
        return new UserRole(ConnectionManager::fetchAssoc("
            SELECT * 
            FROM cp_user_roles 
            WHERE user_id = :user_id AND role_id = :role_id", [
                "user_id" => $user_id,
                "role_id" => $role_id
        ]));
    }
}