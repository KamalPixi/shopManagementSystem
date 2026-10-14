param([string]$PrinterBase64, [string]$PathBase64)
$ErrorActionPreference = 'Stop'
try {
    $printer = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String($PrinterBase64))
    $path = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String($PathBase64))
    Add-Type -TypeDefinition @'
using System;
using System.ComponentModel;
using System.Runtime.InteropServices;
public static class ReceiptSpooler {
    [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Unicode)]
    public class DocInfo {
        [MarshalAs(UnmanagedType.LPWStr)] public string Name = "Shop receipt";
        [MarshalAs(UnmanagedType.LPWStr)] public string OutputFile = null;
        [MarshalAs(UnmanagedType.LPWStr)] public string DataType = "RAW";
    }
    [DllImport("winspool.drv", EntryPoint="OpenPrinterW", CharSet=CharSet.Unicode, SetLastError=true)]
    static extern bool OpenPrinter(string name, out IntPtr handle, IntPtr defaults);
    [DllImport("winspool.drv", EntryPoint="StartDocPrinterW", CharSet=CharSet.Unicode, SetLastError=true)]
    static extern int StartDocPrinter(IntPtr handle, int level, [In] DocInfo info);
    [DllImport("winspool.drv", SetLastError=true)] static extern bool StartPagePrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError=true)] static extern bool WritePrinter(IntPtr handle, byte[] bytes, int count, out int written);
    [DllImport("winspool.drv", SetLastError=true)] static extern bool EndPagePrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError=true)] static extern bool EndDocPrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError=true)] static extern bool AbortPrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError=true)] static extern bool ClosePrinter(IntPtr handle);
    static void Check(bool ok) { if (!ok) throw new Win32Exception(Marshal.GetLastWin32Error()); }
    public static int Send(string name, byte[] bytes) {
        IntPtr handle;
        Check(OpenPrinter(name, out handle, IntPtr.Zero));
        bool started = false;
        try {
            int job = StartDocPrinter(handle, 1, new DocInfo());
            Check(job != 0);
            started = true;
            Check(StartPagePrinter(handle));
            int written;
            Check(WritePrinter(handle, bytes, bytes.Length, out written));
            if (written != bytes.Length) throw new Exception("Windows accepted only part of the receipt.");
            Check(EndPagePrinter(handle));
            Check(EndDocPrinter(handle));
            started = false;
            return job;
        } finally {
            if (started) AbortPrinter(handle);
            ClosePrinter(handle);
        }
    }
}
'@
    $job = [ReceiptSpooler]::Send($printer, [IO.File]::ReadAllBytes($path))
    Write-Output "Queued print job $job"
    exit 0
} catch {
    [Console]::Error.WriteLine($_.Exception.Message)
    exit 1
}
