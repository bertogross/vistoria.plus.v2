<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $timestamps = true;

    protected $connection = 'vpOnboard';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    // Constants representing user roles
    const ROLE_ADMIN = 1;
    const ROLE_EDITOR = 2;
    const ROLE_PUBLISHER = 3;
    const ROLE_VISUALIZATION = 4;
    //const ROLE_PARTNER = 5;

    // Capabilities for each role
    const USER_ROLES = [
        self::ROLE_ADMIN => ['manager', 'edit', 'view', 'audit'],
        self::ROLE_EDITOR => ['view', 'audit'],
        self::ROLE_PUBLISHER => ['edit', 'view'],
        self::ROLE_VISUALIZATION => ['view'],
        //self::ROLE_PARTNER => ['view'],
    ];

    const CAPABILITY_TRANSLATIONS = [
        'manager' => 'Configurações Gerais',
        'edit' => 'Realizar Vistoria',
        'audit' => 'Auditar Vistoria',
        'view' => 'Visualização Analítica',
        //'partial_view' => 'Limitado ao Nível',
    ];

    /**
     * Get the human-readable capability.
     *
     * @param string $capability The capability key.
     * @return string The human-readable capability.
     */
    public static function getHumanReadableCapability($capability)
    {
        return self::CAPABILITY_TRANSLATIONS[$capability] ?? ucfirst(str_replace('_', ' ', $capability));
    }

    /**
     * Get the human-readable name of the user's role.
     *
     * @param int $role The role identifier.
     * @return string The human-readable name of the role.
     */
    public static function getRoleName($role = 1)
    {
        $role = intval($role);

        $roles = [
            self::ROLE_ADMIN => 'Administrativo',
            self::ROLE_EDITOR => 'Auditoria',
            self::ROLE_PUBLISHER => 'Vistoria',
            self::ROLE_VISUALIZATION => 'Visitante',
            //self::ROLE_PARTNER => 'Sócio Investidor',
        ];

        return $roles[$role] ?? 'Função Desconhecida';
    }

    /**
     * Check if the user has a specific role.
     *
     * @param int $role The role identifier.
     * @return bool True if the user has the role, false otherwise.
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }


    public function hasAnyRole(...$roles)
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if the user has a specific capability.
     *
     * @param string $capability The capability to check for.
     * @return bool True if the user has the capability, false otherwise.
     */
    public function hasCapability($capability)
    {
        return in_array($capability, self::USER_ROLES[$this->role] ?? []);
    }


    /**
     * Generate a permissions table based on roles and capabilities.
     *
     * @return string HTML representation of the permissions table.
     */
    public static function generatePermissionsTable()
    {
        $roles = array_keys(self::USER_ROLES);
        $capabilitiesList = array_keys(self::CAPABILITY_TRANSLATIONS);

        $caption = '<div class="row">';
            $caption .= '<div class="col text-end fw-normal fs-12"><i class="ri-checkbox-circle-fill text-success me-1 align-bottom" title="Ok"></i> Habilitado</div>';
            //$caption .= '<div class="col fw-normal fs-12"><i class="ri-error-warning-line text-warning me-1 align-bottom" title="Limitações Administrativas"></i> Limitado pela Atribuição</div>';
            $caption .= '<div class="col text-start fw-normal fs-12"><i class="ri-close-circle-line text-danger me-1 align-bottom" title="Não permitido"></i> Desabilitado</div>';
            //$caption .= '<div class="col fw-normal fs-12"><i class="ri-forbid-2-line text-info me-1 align-bottom" title="Somente visualização"></i> Somente visualização</div>';
        $caption .= '</div>';

        $html = '<div class="card mt-2">';
            $html .= '<div class="card-header fw-bold text-uppercase">Níveis e Permissões</div>';
            $html .= '<div class="card-body">';
                $html .= '<div class="table-responsive">';
                    $html .= '<table class="table table-bordered table-striped mb-0">';
                        $html .= '<thead class="table-light">';
                            $html .= '<tr>';
                                $html .= '<th class="fw-normal">'.$caption.'</th>';

                                foreach ($roles as $roleId) {
                                    $html .= '<th class="text-center">' . self::getRoleName($roleId) . '</th>';
                                }

                            $html .= '</tr>';
                        $html .= '</thead>';
                        $html .= '<tbody>';

                        foreach ($capabilitiesList as $capability) {
                            $html .= '<tr>';
                            //$html .= '<td class="text-end">' . ucfirst(str_replace('_', ' ', $capability)) . ':</td>';
                            $html .= '<td class="text-end">' . self::getHumanReadableCapability($capability) . ':</td>';

                            foreach ($roles as $roleId) {
                                $html .= '<td class="text-center">';
                                if (in_array($capability, self::USER_ROLES[$roleId])) {
                                    $html .= '<i class="ri-checkbox-circle-fill text-success" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Habilitado" title="Habilitado"></i>';
                                }
                                /*
                                else if ($roleId == 4) {
                                    $html .= '<i class="ri-error-warning-line text-warning" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Limitado conforme a Atribuição" title="Limitado conforme a Atribuição"></i>';
                                }
                                */
                                else {
                                    $html .= '<i class="ri-close-circle-line text-danger" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Desabilitado" title="Desabilitado"></i>';
                                }
                                $html .= '</td>';
                            }

                            $html .= '</tr>';
                        }

                        $html .= '</tbody>';
                    $html .= '</table>';
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public static function findUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public static function statusTranslationLabel($status, $icon = true)
    {
        switch ($status) {
            case 'active':
                $riIcon = $icon ? '<i class="ri-checkbox-circle-line fs-17 align-middle me-1"></i>' : '';
                return '<span class="text-success">'.$riIcon.'Ativo</span>';
                break;
            case 'inactive':
                $riIcon = $icon ? '<i class="ri-close-circle-line fs-17 align-middle me-1"></i>' : '';
                return '<span class="text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Inoperante pois foi por você desativado">'.$riIcon.'Inativo</span>';
                break;
            case 'revoked':
                $riIcon = $icon ? '<i class="ri-alert-line fs-17 align-middle me-1"></i>' : '';
                return '<span class="text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Quando o usuário revogou a conexão">'.$riIcon.'Desconectado</span>';
                break;
            case 'waiting':
                $riIcon = $icon ? '<i class="ri-information-line fs-17 align-middle me-1"></i>' : '';
                return '<span class="text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Aguardando o aceite de seu convite">'.$riIcon.'Aguardando</span>';
                break;
            default:
                $riIcon = $icon ? '<i class="ri-question-line fs-17 align-middle me-1"></i>' : '';
                return '<span class="text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Status desconhecido">'.$riIcon.'Desconhecido</span>';
        }
    }


    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token)
    {
        $url = 'your-password-reset-link-url-here/'.$token;

        $this->notify(new ResetPasswordNotification($url));
    }

}
