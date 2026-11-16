<?php

namespace App\Http\Controllers;

use App\Models\EmailHost;
use App\Models\EmailRelay;
use App\Models\Server;
use App\Models\Service;
use Illuminate\Http\Request;

class MasterSettingController extends Controller
{
    public function servers()
    {
        $servers = Server::orderBy('id', 'asc')->get();
        return view('settings.servers', compact('servers'));
    }

    public function storeServer(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:100',
            'domain' => 'required|string|max:100',
            'color'  => 'required|string|max:50',
        ]);

        Server::create([
            'name'   => $validated['name'],
            'domain' => $validated['domain'],
            'color'  => $validated['color'],
            'flag'   => 'Y',
        ]);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil menyimpan Server "' . $validated['name'] . '".',
        ]);
    }

    public function updateServer(Request $request, Server $server)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:100',
            'domain' => 'required|string|max:100',
            'color'  => 'required|string|max:50',
        ]);

        $server->update($validated);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil memperbarui Server "' . $server->name . '".',
        ]);
    }

    public function services()
    {
        $services = Service::with('server')->orderBy('id', 'asc')->get();
        $servers = Server::where('flag', 'Y')->orderBy('id', 'asc')->get();

        return view('settings.services', compact('services', 'servers'));
    }

    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'service'   => 'required|string|max:100',
            'server_id' => 'required|integer',
            'color'     => 'required|string|max:50',
        ]);

        Service::create([
            'service'   => $validated['service'],
            'server_id' => $validated['server_id'],
            'color'     => $validated['color'],
            'flag'      => 'Y',
        ]);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil menyimpan Content Service.',
        ]);
    }

    public function updateService(Request $request, Service $service)
    {
        $validated = $request->validate([
            'service'   => 'required|string|max:100',
            'server_id' => 'required|integer',
            'color'     => 'required|string|max:50',
        ]);

        $service->update($validated);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil memperbarui Content Service.',
        ]);
    }

    public function hosts()
    {
        $hosts = EmailHost::with('server')->orderBy('id', 'asc')->get();
        $servers = Server::where('flag', 'Y')->orderBy('id', 'asc')->get();

        return view('settings.hosts', compact('hosts', 'servers'));
    }

    public function storeHost(Request $request)
    {
        $validated = $request->validate([
            'host'      => 'required|string|max:100',
            'server_id' => 'required|integer',
        ]);

        EmailHost::create([
            'host'      => $validated['host'],
            'server_id' => $validated['server_id'],
            'flag'      => 'Y',
        ]);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil menyimpan Host SMTP "' . $validated['host'] . '".',
        ]);
    }

    public function updateHost(Request $request, EmailHost $host)
    {
        $validated = $request->validate([
            'host'      => 'required|string|max:100',
            'server_id' => 'required|integer',
        ]);

        $host->update($validated);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil memperbarui Host SMTP "' . $host->host . '".',
        ]);
    }

    public function relays()
    {
        $relays = EmailRelay::with('emailHost.server')->orderBy('id', 'asc')->get();
        $hosts = EmailHost::with('server')->where('flag', 'Y')->orderBy('id', 'asc')->get();

        return view('settings.relays', compact('relays', 'hosts'));
    }

    public function storeRelay(Request $request)
    {
        $validated = $request->validate([
            'host_id'     => 'required|integer',
            'email'       => 'required|email|max:100',
            'email_from'  => 'required|email|max:255',
            'email_alias' => 'nullable|string|max:255',
            'password'    => 'required|string|max:255',
            'port'        => 'required|integer',
        ]);

        EmailRelay::create([
            'host_id'     => $validated['host_id'],
            'email'       => $validated['email'],
            'email_from'  => $validated['email_from'],
            'email_alias' => (string) ($validated['email_alias'] ?? ''),
            'password'    => $validated['password'],
            'port'        => $validated['port'],
            'flag'        => 'Y',
        ]);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil menyimpan akun SMTP Relay "' . $validated['email'] . '".',
        ]);
    }

    public function updateRelay(Request $request, EmailRelay $relay)
    {
        $validated = $request->validate([
            'host_id'     => 'required|integer',
            'email'       => 'required|email|max:100',
            'email_from'  => 'required|email|max:255',
            'email_alias' => 'nullable|string|max:255',
            'password'    => 'nullable|string|max:255',
            'port'        => 'required|integer',
            'flag'        => 'nullable|in:Y,N',
        ]);

        $data = [
            'host_id'     => $validated['host_id'],
            'email'       => $validated['email'],
            'email_from'  => $validated['email_from'],
            'email_alias' => (string) ($validated['email_alias'] ?? ''),
            'port'        => $validated['port'],
            'flag'        => $validated['flag'] ?? $relay->flag,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $relay->update($data);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil memperbarui akun SMTP Relay "' . $relay->email . '".',
        ]);
    }
}

