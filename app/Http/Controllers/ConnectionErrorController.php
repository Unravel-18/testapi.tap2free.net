<?php

namespace App\Http\Controllers;

use Validator;
use Redirect;

use App\Models\Api;
use App\Models\Server;
use App\Models\ConnectionError;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Storage;
use Carbon\Carbon;
use File;
use Response;
use DB;
use Session;

class ConnectionErrorController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function apiAddError(Request $request)
    {
        $this->authHeader();
        $this->ctrIp($request);

        $status = 0;
        $error = null;

        if ($request->ip && $request->country) {
            if (strlen($request->ip) >= 7 && strlen($request->ip) <= 17 && strlen($request->
                country) >= 1 && strlen($request->country) <= 4) {
                if (Server::where('ip', '=', $request->ip)->count() > 0) {
                    $ConnectionError = ConnectionError::where('ip', '=', $request->ip)->where('country',
                        '=', $request->country)->where('error_at', '=', date('Y-m-d H:i:00'))->first();

                    if (!$ConnectionError) {
                        $ConnectionError = new ConnectionError;

                        $ConnectionError->ip = $request->ip;
                        $ConnectionError->country = $request->country;
                        $ConnectionError->error_at = date('Y-m-d H:i:00');
                        $ConnectionError->count_errors = 0;
                    }

                    $ConnectionError->count_errors = $ConnectionError->count_errors + 1;

                    $ConnectionError->save();

                    $status = 1;
                } else {
                    $error = 3;
                }
            } else {
                $error = 2;
            }
        } else {
            $error = 1;
        }

        return Response::json(['status' => $status, 'error' => $error], 200, [],
            JSON_HEX_TAG);
    }

    public function index(Request $request)
    {
        $this->params['apis'] = Api::get();

        if (isset($_GET['sort'])) {
            $this->params['sort'] = $_GET['sort'];
        } else {
            $this->params['sort'] = 'count_errors';
        }

        $sort = preg_replace("#[^a-zA-Z_]#", '', $this->params['sort']);

        $query = ConnectionError::select([
            'connection_errors.ip',
            DB::raw('MAX(`connection_errors`.`id`) AS id'),
            DB::raw('GROUP_CONCAT(DISTINCT `connection_errors`.`country` SEPARATOR \', \') AS country'),
            DB::raw('MIN(`connection_errors`.`error_at`) AS min_error_at'),
            DB::raw('MAX(`connection_errors`.`error_at`) AS max_error_at'),
            DB::raw('SUM(`connection_errors`.`count_errors`) AS count_errors'),
            DB::raw('SUM(IF(`connection_errors`.`error_at` >= \''.date('Y-m-d H:i:00', time() - 3600).'\', `connection_errors`.`count_errors`, 0)) AS count_errors_last_hour'),
        ])->orderBy('count_errors', 'desc');

        switch ($sort) {
            case 'not_available_at':
                $sort = DB::raw('IF(not_available_at, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(not_available_at), null)');

                break;
        }
        
        $query->groupBy([
            'connection_errors.ip',
        ]);

        //$query->orderBy($sort, stripos($this->params['sort'], '-') === 0 ? 'desc' : 'asc');

        if (is_array($request->search)) {
            foreach ($request->search as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'error_at':
                    case 'name':
                    case 'ip':
                        $query->where($key, 'like', '%' . urldecode($value) . '%');

                        break;
                    default:
                        $query->where($key, '=', urldecode($value));

                        break;
                }
            }
        }

        $this->params['connection_errors'] = $query->paginate(800);

        return $this->view('connection_errors.index');
    }

    public function destroy(Request $request)
    {
        $connection_error = ConnectionError::where('id', '=', $request->connection_error_id)->firstOrFail();
        
        ConnectionError::where('ip', '=', $connection_error->ip)->delete();
        
        return Redirect::route('apis.connection_errors.index');
    }

    public function destroyIpCountry(Request $request)
    {
        $connection_error = ConnectionError::where('id', '=', $request->connection_error_id)->firstOrFail();
        
        ConnectionError::where('ip', '=', $connection_error->ip)->where('country', '=', $connection_error->country)->delete();
        
        return Redirect::route('apis.connection_errors.ip', ['ip' => $connection_error->ip]);
    }

    public function ip(Request $request)
    {
        $this->params['apis'] = Api::get();

        if (isset($_GET['sort'])) {
            $this->params['sort'] = $_GET['sort'];
        } else {
            $this->params['sort'] = 'count_errors';
        }

        $sort = preg_replace("#[^a-zA-Z_]#", '', $this->params['sort']);
        
        $this->params['ip'] = $request->ip;

        $query = ConnectionError::select([
            'connection_errors.country',
            DB::raw('MAX(`connection_errors`.`id`) AS id'),
            DB::raw('GROUP_CONCAT(DISTINCT `connection_errors`.`ip` SEPARATOR \', \') AS ip'),
            DB::raw('MIN(`connection_errors`.`error_at`) AS min_error_at'),
            DB::raw('MAX(`connection_errors`.`error_at`) AS max_error_at'),
            DB::raw('SUM(`connection_errors`.`count_errors`) AS count_errors'),
            DB::raw('SUM(IF(`connection_errors`.`error_at` >= \''.date('Y-m-d H:i:00', time() - 3600).'\', `connection_errors`.`count_errors`, 0)) AS count_errors_last_hour'),
        ])->where('ip', '=', $request->ip)->orderBy('count_errors', 'desc');

        switch ($sort) {
            case 'not_available_at':
                $sort = DB::raw('IF(not_available_at, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(not_available_at), null)');

                break;
        }
        
        $query->groupBy([
            'connection_errors.country',
        ]);

        //$query->orderBy($sort, stripos($this->params['sort'], '-') === 0 ? 'desc' : 'asc');

        if (is_array($request->search)) {
            foreach ($request->search as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'name':
                    case 'error_at':
                    case 'first_error_at':
                    case 'last_error_at':
                    case 'ip':
                        $query->where($key, 'like', '%' . urldecode($value) . '%');

                        break;
                    default:
                        $query->where($key, '=', urldecode($value));

                        break;
                }
            }
        }

        $this->params['connection_errors'] = $query->paginate(800);

        return $this->view('connection_errors.index_ip');
    }

    public function deleteIp(Request $request)
    {
        $items = ConnectionError::whereIn('id', $request->items_id)->get();

        foreach ($items as $item) {
            ConnectionError::where('ip', '=', $item->ip)->delete();
        }

        return Response::json(['status' => true]);
    }

    public function deleteIpCountry(Request $request)
    {
        $items = ConnectionError::whereIn('id', $request->items_id)->get();

        foreach ($items as $item) {
            ConnectionError::where('ip', '=', $item->ip)->where('country', '=', $item->country)->delete();
        }

        return Response::json(['status' => true]);
    }
}
