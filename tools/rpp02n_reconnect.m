#import <IOBluetooth/IOBluetooth.h>
#import <Foundation/Foundation.h>

static IOBluetoothDevice *findTarget(NSString *target) {
    NSArray *devices = [IOBluetoothDevice pairedDevices];
    for (IOBluetoothDevice *d in devices) {
        if ([d.name.lowercaseString containsString:target.lowercaseString]) return d;
    }
    return nil;
}

static const char *addrOf(IOBluetoothDevice *d) {
    NSString *a = [d addressString];
    return a ? a.UTF8String : "?";
}

static IOReturn tryChannel(IOBluetoothDevice *dev, BluetoothRFCOMMChannelID ch) {
    IOBluetoothRFCOMMChannel *channel = nil;
    IOReturn rc = [dev openRFCOMMChannelSync:&channel withChannelID:ch delegate:nil];
    if (rc != kIOReturnSuccess || channel == nil) return rc;
    printf("   channel %u OPEN OK\n", (unsigned)ch);

    const char *msg = "=== RECONNECT TEST ===\nHELLO AGAIN\n\n\n\n";
    uint8_t buf[128];
    size_t n = strlen(msg);
    memcpy(buf, msg, n);
    printf("   write -> %d\n", [channel write:buf length:(UInt16)n]);
    usleep(400000);

    uint8_t cutc[4] = {0x1D, 0x56, 0x42, 0x00};
    printf("   cut   -> %d\n", [channel write:cutc length:4]);
    usleep(600000);

    [channel closeChannel];
    [dev closeConnection];
    return kIOReturnSuccess;
}

int main(int argc, const char * argv[]) {
    @autoreleasepool {
        NSString *target = argc > 1 ? [NSString stringWithUTF8String:argv[1]] : @"RPP02N";
        IOBluetoothDevice *dev = findTarget(target);
        if (!dev) { printf("[FAIL] %s not paired\n", target.UTF8String); return 2; }
        printf("Device: %s %s\n", dev.name.UTF8String, addrOf(dev));

        for (int attempt = 1; attempt <= 3; attempt++) {
            printf("\nAttempt %d: force disconnect...\n", attempt);
            IOBluetoothRFCOMMChannel *chan = nil;
            IOReturn rc = [dev openRFCOMMChannelSync:&chan withChannelID:1 delegate:nil];
            if (rc == kIOReturnSuccess && chan) { [chan closeChannel]; }
            [dev closeConnection];
            usleep(800000);

            printf("   reconnect (IOBluetoothDevice openConnection)...\n");
            rc = [dev openConnection];
            if (rc != kIOReturnSuccess) { printf("   openConnection rc=%d\n", rc); usleep(1000000); continue; }
            usleep(1200000);

            printf("   retry SPP channel 1...\n");
            rc = tryChannel(dev, 1);
            if (rc == kIOReturnSuccess) { printf("\nRESULT: channel alive, data sent. Printed?\n"); return 0; }
            printf("   SPP rc=0x%08X\n", rc);
            usleep(1200000);
        }
        printf("\nRESULT: after 3 reconnect attempts SPP still refused (0xE00002BC area).\n");
        printf("=> macOS cannot establish data session to this printer. Not a code issue.\n");
        return 3;
    }
}